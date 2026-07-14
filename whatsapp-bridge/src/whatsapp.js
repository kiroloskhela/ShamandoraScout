import path from 'node:path';
import { fileURLToPath } from 'node:url';
import makeWASocket, {
  DisconnectReason,
  fetchLatestBaileysVersion,
  useMultiFileAuthState,
} from '@whiskeysockets/baileys';
import { Boom } from '@hapi/boom';
import pino from 'pino';
import QRCode from 'qrcode';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const AUTH_DIR = path.join(__dirname, '..', 'auth_session');

const logger = pino({ level: process.env.LOG_LEVEL || 'info' });

let sock = null;
let connected = false;
let latestQrDataUrl = null;
let reconnectAttempt = 0;
let starting = false;

function toJid(fullNumber) {
  const digits = String(fullNumber || '').replace(/\D+/g, '');
  if (!digits) {
    throw new Error('Invalid phone number');
  }
  return `${digits}@s.whatsapp.net`;
}

export function getStatus() {
  return {
    ok: true,
    connected,
    qr: connected ? null : latestQrDataUrl,
  };
}

export async function sendText(fullNumber, message) {
  if (!sock || !connected) {
    const err = new Error('WhatsApp not connected');
    err.code = 'NOT_CONNECTED';
    throw err;
  }

  const jid = toJid(fullNumber);
  const result = await sock.sendMessage(jid, { text: String(message) });
  return {
    ok: true,
    to: fullNumber,
    messageId: result?.key?.id || null,
  };
}

async function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

export async function startWhatsApp() {
  if (starting) {
    return;
  }
  starting = true;

  try {
    const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);
    const { version } = await fetchLatestBaileysVersion();

    sock = makeWASocket({
      version,
      auth: state,
      logger: pino({ level: 'silent' }),
      syncFullHistory: false,
      markOnlineOnConnect: false,
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
