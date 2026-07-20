import path from 'node:path';
import { fileURLToPath } from 'node:url';
import makeWASocket, {
  DisconnectReason,
  fetchLatestBaileysVersion,
  makeCacheableSignalKeyStore,
  useMultiFileAuthState,
} from '@whiskeysockets/baileys';
import { Boom } from '@hapi/boom';
import pino from 'pino';
import QRCode from 'qrcode';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const AUTH_DIR = path.join(__dirname, '..', 'auth_session');

const logger = pino({ level: process.env.LOG_LEVEL || 'info'});

let sock = null;
let connected = false;
let latestQrDataUrl = null;
let reconnectAttempt = 0;
let starting = false;

/** Recent outbound messages for Baileys retry / decrypt recovery */
const recentMessages = new Map();
const RECENT_MESSAGE_LIMIT = 300;

/** Serialize sends so bulk QR blasts don't corrupt sessions */
let sendChain = Promise.resolve();

function rememberMessage(key, message) {
  if (!key?.id) return;
  const id = key.id;
  recentMessages.set(id, message);
  if (recentMessages.size > RECENT_MESSAGE_LIMIT) {
    const oldest = recentMessages.keys().next().value;
    recentMessages.delete(oldest);
  }
}

function toJid(fullNumber) {
  const digits = String(fullNumber || '').replace(/\D+/g, '');
  if (!digits) {
    throw new Error('Invalid phone number');
  }
  return `${digits}@s.whatsapp.net`;
}

async function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

/**
 * Warm crypto session with the peer before sending (reduces "Waiting for this message").
 */
async function prepareRecipient(jid) {
  if (!sock) return;

  try {
    if (typeof sock.onWhatsApp === 'function') {
      const info = await sock.onWhatsApp(jid);
      const row = Array.isArray(info) ? info[0] : null;
      if (row && row.exists === false) {
        throw new Error('WhatsApp number does not exist');
      }
    }
  } catch (err) {
    if (err?.message === 'WhatsApp number does not exist') {
      throw err;
    }
    logger.warn({ err: err?.message, jid }, 'onWhatsApp check failed; continuing');
  }

  try {
    if (typeof sock.assertSessions === 'function') {
      await sock.assertSessions([jid], true);
    }
  } catch (err) {
    logger.warn({ err: err?.message, jid }, 'assertSessions failed; continuing');
  }

  try {
    if (typeof sock.presenceSubscribe === 'function') {
      await sock.presenceSubscribe(jid);
    }
  } catch {
    // optional
  }

  // Small pause so Signal session can settle before media/text
  await sleep(400);
}

function enqueueSend(task) {
  const run = sendChain.then(task, task);
  sendChain = run.catch(() => {});
  return run;
}

export function getStatus() {
  return {
    ok: true,
    connected,
    qr: connected ? null : latestQrDataUrl,
  };
}

export async function sendText(fullNumber, message) {
  return enqueueSend(async () => {
    if (!sock || !connected) {
      const err = new Error('WhatsApp not connected');
      err.code = 'NOT_CONNECTED';
      throw err;
    }

    const jid = toJid(fullNumber);
    await prepareRecipient(jid);

    const content = { text: String(message) };
    const result = await sock.sendMessage(jid, content);
    rememberMessage(result?.key, content);

    return {
      ok: true,
      to: fullNumber,
      messageId: result?.key?.id || null,
    };
  });
}

/**
 * Send an image (base64 PNG/JPEG) with optional caption.
 */
export async function sendImage(fullNumber, imageBase64, caption = '', mimeType = 'image/png') {
  return enqueueSend(async () => {
    if (!sock || !connected) {
      const err = new Error('WhatsApp not connected');
      err.code = 'NOT_CONNECTED';
      throw err;
    }

    const raw = String(imageBase64 || '').replace(/^data:[^;]+;base64,/, '');
    if (!raw) {
      throw new Error('image_base64 is required');
    }

    const jid = toJid(fullNumber);
    await prepareRecipient(jid);

    const content = {
      image: Buffer.from(raw, 'base64'),
      caption: caption ? String(caption) : undefined,
      mimetype: mimeType || 'image/png',
    };

    const result = await sock.sendMessage(jid, content);
    rememberMessage(result?.key, content);

    return {
      ok: true,
      to: fullNumber,
      messageId: result?.key?.id || null,
    };
  });
}

export async function startWhatsApp() {
  if (starting) {
    return;
  }
  starting = true;

  try {
    const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);
    const { version } = await fetchLatestBaileysVersion();
    const silent = pino({ level: 'silent' });

    sock = makeWASocket({
      version,
      auth: {
        creds: state.creds,
        keys: makeCacheableSignalKeyStore(state.keys, silent),
      },
      logger: silent,
      syncFullHistory: false,
      markOnlineOnConnect: false,
      // Critical for WhatsApp retry requests when a phone cannot decrypt yet
      getMessage: async (key) => {
        if (key?.id && recentMessages.has(key.id)) {
          return recentMessages.get(key.id);
        }
        return undefined;
      },
    });

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', async (update) => {
      const { connection, lastDisconnect, qr } = update;

      if (qr) {
        try {
          latestQrDataUrl = await QRCode.toDataURL(qr);
          logger.info('QR ready — scan via SuperAdmin /whatsapp/status or terminal');
        } catch (e) {
          logger.error({ err: e }, 'Failed to render QR data URL');
          latestQrDataUrl = null;
        }
      }

      if (connection === 'open') {
        connected = true;
        latestQrDataUrl = null;
        reconnectAttempt = 0;
        logger.info('WhatsApp connected');
      }

      if (connection === 'close') {
        connected = false;
        const statusCode =
          lastDisconnect?.error instanceof Boom
            ? lastDisconnect.error.output?.statusCode
            : lastDisconnect?.error?.output?.statusCode;

        const loggedOut = statusCode === DisconnectReason.loggedOut;
        logger.warn({ statusCode, loggedOut }, 'WhatsApp disconnected');

        if (loggedOut) {
          latestQrDataUrl = null;
          logger.error(
            'Logged out of WhatsApp. Delete auth_session only if you intend to re-pair, then restart.'
          );
          starting = false;
          return;
        }

        reconnectAttempt += 1;
        const delay = Math.min(30_000, 1000 * 2 ** Math.min(reconnectAttempt, 5));
        logger.info({ delay, reconnectAttempt }, 'Reconnecting with saved session');
        starting = false;
        await sleep(delay);
        startWhatsApp().catch((err) => logger.error({ err }, 'Reconnect failed'));
        return;
      }
    });
  } catch (err) {
    logger.error({ err }, 'Failed to start WhatsApp socket');
    starting = false;
    const delay = Math.min(30_000, 1000 * 2 ** Math.min(reconnectAttempt + 1, 5));
    reconnectAttempt += 1;
    await sleep(delay);
    return startWhatsApp();
  }

  starting = false;
}
