import { existsSync, mkdirSync, readFileSync, rmSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import makeWASocket, {
  makeCacheableSignalKeyStore,
  useMultiFileAuthState,
} from '@whiskeysockets/baileys';
import { Boom } from '@hapi/boom';
import pino from 'pino';
import QRCode from 'qrcode';
import { pickSendJid, toPnJid } from './jid.js';
import {
  credsLookRegistered,
  isPairingStatus,
  nextLogoutAction,
  reconnectDelayMs,
  shouldWatchdogReconnect,
} from './reconnectPolicy.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const AUTH_DIR = path.join(__dirname, '..', 'auth_session');
const CREDS_FILE = path.join(AUTH_DIR, 'creds.json');
// keepAliveIntervalMs (socket) + HEARTBEAT_MS (presence) + WATCHDOG_MS (restart from disk)
const HEARTBEAT_MS = 5 * 60 * 1000;
const WATCHDOG_MS = 60 * 1000;

const logger = pino({ level: process.env.LOG_LEVEL || 'info'});

let sock = null;
let connected = false;
let latestQrDataUrl = null;
let reconnectAttempt = 0;
let starting = false;
let reconnecting = false;
let pairingRequired = false;
let lastDisconnectCode = null;
let consecutiveLoggedOut = 0;
let reconnectTimer = null;
let heartbeatTimer = null;
let watchdogTimer = null;

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

async function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

async function lookupLidForPn(pnJid) {
  const mapping = sock?.signalRepository?.lidMapping;
  if (!mapping || typeof mapping.getLIDForPN !== 'function') {
    return null;
  }

  try {
    const lid = await mapping.getLIDForPN(pnJid);
    return typeof lid === 'string' && lid.includes('@lid') ? lid : null;
  } catch (err) {
    logger.warn({ err: err?.message }, 'LID lookup failed; continuing with PN');
    return null;
  }
}

async function resolveSendJid(fullNumber) {
  const pnJid = toPnJid(fullNumber);
  let exists;
  let onWaJid = null;

  try {
    if (typeof sock?.onWhatsApp === 'function') {
      const info = await sock.onWhatsApp(pnJid);
      const row = Array.isArray(info) ? info[0] : null;
      if (row) {
        exists = row.exists;
        onWaJid = row.jid ?? null;
      }
    }
  } catch (err) {
    if (err?.message === 'WhatsApp number does not exist') {
      throw err;
    }
    logger.warn({ err: err?.message }, 'onWhatsApp check failed; continuing');
  }

  const lid = await lookupLidForPn(pnJid);
  const sendJid = pickSendJid({ exists, lid, jid: onWaJid }, pnJid);
  logger.info({ addressing: sendJid.includes('@lid') ? 'lid' : 'pn' }, 'Resolved send JID');

  return sendJid;
}

/**
 * Warm crypto session with the already-resolved send JID.
 */
async function prepareRecipient(jid) {
  if (!sock) return;

  try {
    if (typeof sock.assertSessions === 'function') {
      await sock.assertSessions([jid], true);
    }
  } catch (err) {
    logger.warn({ err: err?.message }, 'assertSessions failed; continuing');
  }

  try {
    if (typeof sock.presenceSubscribe === 'function') {
      await sock.presenceSubscribe(jid);
    }
  } catch {
    // optional
  }

  await sleep(400);
}

function enqueueSend(task) {
  const run = sendChain.then(task, task);
  sendChain = run.catch(() => {});
  return run;
}

function hasReusableSession() {
  if (!existsSync(CREDS_FILE)) {
    return false;
  }

  try {
    return credsLookRegistered(JSON.parse(readFileSync(CREDS_FILE, 'utf8')));
  } catch {
    return false;
  }
}

function stopHeartbeat() {
  if (heartbeatTimer) {
    clearInterval(heartbeatTimer);
    heartbeatTimer = null;
  }
}

function startHeartbeat() {
  stopHeartbeat();
  heartbeatTimer = setInterval(() => {
    if (!sock || !connected) {
      return;
    }
    Promise.resolve(sock.sendPresenceUpdate('available')).catch((err) => {
      logger.warn({ err: err?.message }, 'Presence heartbeat failed');
    });
  }, HEARTBEAT_MS);
}

function disposeSocket() {
  stopHeartbeat();
  const current = sock;
  sock = null;
  connected = false;
  if (!current) {
    return;
  }
  try {
    current.ev.removeAllListeners();
  } catch {
    // ignore
  }
  try {
    current.end(undefined);
  } catch {
    // ignore
  }
  try {
    current.ws?.close();
  } catch {
    // ignore
  }
}

function resetAuthDir() {
  try {
    rmSync(AUTH_DIR, { recursive: true, force: true });
  } catch (err) {
    logger.warn({ err: err?.message }, 'Failed to clear auth_session');
  }
  mkdirSync(AUTH_DIR, { recursive: true });
}

function scheduleReconnect(statusCode, reason, { force = false } = {}) {
  if (pairingRequired && !force) {
    logger.warn({ statusCode, reason }, 'Not auto-reconnecting; pairing required');
    return;
  }
  if (reconnectTimer || starting) {
    return;
  }

  reconnecting = true;
  reconnectAttempt += 1;
  const delay = reconnectDelayMs(statusCode, reconnectAttempt);
  logger.info({ delay, reconnectAttempt, statusCode, reason }, 'Reconnecting with saved session');
  reconnectTimer = setTimeout(() => {
    reconnectTimer = null;
    reconnecting = false;
    startWhatsApp().catch((err) => logger.error({ err: err?.message }, 'Reconnect failed'));
  }, delay);
}

function ensureWatchdog() {
  if (watchdogTimer) {
    return;
  }
  watchdogTimer = setInterval(() => {
    const busy = starting || reconnecting || Boolean(reconnectTimer);
    if (!shouldWatchdogReconnect({
      connected,
      hasReusableSession: hasReusableSession(),
      pairingRequired,
      busy,
    })) {
      return;
    }
    logger.warn('Watchdog reconnecting from saved session');
    startWhatsApp().catch((err) => logger.error({ err: err?.message }, 'Watchdog reconnect failed'));
  }, WATCHDOG_MS);
}

export function getStatus() {
  return {
    ok: true,
    connected,
    qr: connected ? null : latestQrDataUrl,
    hasReusableSession: hasReusableSession(),
    pairingRequired,
    reconnecting: starting || reconnecting || Boolean(reconnectTimer),
    lastDisconnectCode,
  };
}

export async function reconnectFromDisk() {
  if (connected) {
    return { ...getStatus(), skipped: true, reason: 'already_connected' };
  }
  if (starting || reconnecting || reconnectTimer) {
    return { ...getStatus(), skipped: true, reason: 'already_reconnecting' };
  }

  consecutiveLoggedOut = 0;
  if (reconnectTimer) {
    clearTimeout(reconnectTimer);
    reconnectTimer = null;
  }
  logger.info('Manual reconnect from saved session');
  await startWhatsApp();

  return getStatus();
}

export async function sendText(fullNumber, message) {
  return enqueueSend(async () => {
    if (!sock || !connected) {
      const err = new Error('WhatsApp not connected');
      err.code = 'NOT_CONNECTED';
      throw err;
    }

    const jid = await resolveSendJid(fullNumber);
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

    const jid = await resolveSendJid(fullNumber);
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
  if (starting || reconnecting) {
    return;
  }
  starting = true;
  ensureWatchdog();
  disposeSocket();

  try {
    const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);
    const silent = pino({ level: 'silent' });

    sock = makeWASocket({
      auth: {
        creds: state.creds,
        keys: makeCacheableSignalKeyStore(state.keys, silent),
      },
      logger: silent,
      syncFullHistory: false,
      markOnlineOnConnect: true,
      keepAliveIntervalMs: 20_000,
      connectTimeoutMs: 60_000,
      browser: ['ShamandoraScout', 'Chrome', '126.0.0'],
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
          logger.error({ err: e?.message }, 'Failed to render QR data URL');
          latestQrDataUrl = null;
        }
      }

      if (connection === 'open') {
        connected = true;
        latestQrDataUrl = null;
        reconnectAttempt = 0;
        consecutiveLoggedOut = 0;
        pairingRequired = false;
        lastDisconnectCode = null;
        starting = false;
        reconnecting = false;
        startHeartbeat();
        logger.info('WhatsApp connected');
      }

      if (connection === 'close') {
        stopHeartbeat();
        connected = false;
        starting = false;
        latestQrDataUrl = null;
        const statusCode =
          lastDisconnect?.error instanceof Boom
            ? lastDisconnect.error.output?.statusCode
            : lastDisconnect?.error?.output?.statusCode;
        lastDisconnectCode = statusCode ?? null;

        const pairingStatus = isPairingStatus(statusCode);
        logger.warn({ statusCode, pairingStatus, pairingRequired }, 'WhatsApp disconnected');

        disposeSocket();

        if (pairingRequired) {
          scheduleReconnect(statusCode, 'pair-qr', { force: true });
          return;
        }

        if (pairingStatus) {
          consecutiveLoggedOut += 1;
          const action = nextLogoutAction(consecutiveLoggedOut);
          if (action === 'pairing_required') {
            pairingRequired = true;
            resetAuthDir();
            logger.error(
              'WhatsApp unlinked this device. Cleared auth_session so a new QR can be issued.'
            );
            scheduleReconnect(statusCode, 'pair-qr', { force: true });
            return;
          }
          logger.warn('Logged out once — retrying saved session without deleting auth_session');
          scheduleReconnect(statusCode, 'logged-out-retry');
          return;
        }

        consecutiveLoggedOut = 0;
        scheduleReconnect(statusCode, 'disconnected');
      }
    });
  } catch (err) {
    logger.error({ err: err?.message }, 'Failed to start WhatsApp socket');
    starting = false;
    disposeSocket();
    scheduleReconnect(lastDisconnectCode, 'start-failed');
  }
}
