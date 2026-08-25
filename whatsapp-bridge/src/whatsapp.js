import { existsSync, mkdirSync, readFileSync, rmSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import makeWASocket, {
  makeCacheableSignalKeyStore,
  useMultiFileAuthState,
  USyncQuery,
  USyncUser,
} from '@whiskeysockets/baileys';
import { Boom } from '@hapi/boom';
import pino from 'pino';
import QRCode from 'qrcode';
import {
  extractTrustedContactToken,
  isLidJid,
  lidFromMappingRecord,
  newChatCapBlocksSend,
  normalizeLid,
  pickIssuanceJid,
  pickSendJid,
  pnUserPart,
  toPnJid,
  usyncRowMatchesPn,
} from './jid.js';
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
/** One crypto rebuild per recipient per live socket (stale sessions survive pm2 restart) */
const refreshedCryptoUsers = new Set();

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

async function lidFromLocalStore(pnJid) {
  try {
    const stored = await sock.authState.keys.get('lid-mapping', [pnUserPart(pnJid)]);
    return lidFromMappingRecord(stored, pnJid);
  } catch {
    return null;
  }
}

async function lookupLidForPn(pnJid) {
  const mapping = sock?.signalRepository?.lidMapping;
  if (!mapping || typeof mapping.getLIDForPN !== 'function') {
    return null;
  }

  try {
    const lid = await mapping.getLIDForPN(pnJid);
    return normalizeLid(lid);
  } catch (err) {
    logger.warn({ err: err?.message }, 'LID lookup failed');
    return null;
  }
}

async function usyncContactAndLid(pnJid) {
  if (typeof sock?.executeUSyncQuery !== 'function') {
    return { exists: undefined, lid: null, jid: null };
  }

  const user = pnJid.split('@')[0]?.split(':')[0] || '';
  const phone = `+${user.replace(/^\+/, '')}`;

  try {
    const query = new USyncQuery()
      .withContactProtocol()
      .withLIDProtocol()
      .withContext('interactive')
      .withUser(new USyncUser().withId(pnJid).withPhone(phone));
    const results = await sock.executeUSyncQuery(query);
    const list = Array.isArray(results?.list) ? results.list : [];
    const row =
      list.find((item) => usyncRowMatchesPn(item, pnJid)) ||
      (list.length === 1 ? list[0] : null);
    if (!row) {
      return { exists: undefined, lid: null, jid: null };
    }

    const lid = normalizeLid(typeof row.lid === 'string' ? row.lid : null);
    return {
      exists: typeof row.contact === 'boolean' ? row.contact : undefined,
      lid,
      jid: row.id ?? null,
    };
  } catch (err) {
    logger.warn({ err: err?.message }, 'USync contact/LID lookup failed');
    return { exists: undefined, lid: null, jid: null };
  }
}

async function resolveSendJid(fullNumber) {
  const pnJid = toPnJid(fullNumber);
  const cachedLid = await lidFromLocalStore(pnJid);
  let lid = cachedLid;
  let exists;
  let onWaJid = null;

  if (!lid) {
    const usync = await usyncContactAndLid(pnJid);
    exists = usync.exists;
    onWaJid = usync.jid;
    lid = usync.lid || await lookupLidForPn(pnJid);
  }

  if (exists === undefined && !lid) {
    try {
      if (typeof sock?.onWhatsApp === 'function') {
        const info = await sock.onWhatsApp(pnJid);
        const row = Array.isArray(info) ? info[0] : null;
        if (row) {
          exists = row.exists;
          onWaJid = row.jid ?? onWaJid;
        }
      }
    } catch (err) {
      if (err?.message === 'WhatsApp number does not exist') {
        throw err;
      }
      logger.warn({ err: err?.message }, 'onWhatsApp check failed; continuing');
    }

    if (!lid) {
      lid = await lookupLidForPn(pnJid);
    }
  }

  const sendJid = pickSendJid({ exists, lid, jid: onWaJid }, pnJid);
  const hasToken = isLidJid(sendJid) && (await hasUsableTcToken(sendJid));
  const needsNewChatHandshake = isLidJid(sendJid) && !hasToken;
  logger.info(
    {
      addressing: sendJid.includes('@lid') ? 'lid' : 'pn',
      handshake: needsNewChatHandshake,
      storedLid: Boolean(cachedLid),
    },
    'Resolved send JID'
  );

  return {
    sendJid,
    pnJid,
    cachedLid: Boolean(cachedLid),
    needsNewChatHandshake,
  };
}

async function hasUsableTcToken(storageJid) {
  try {
    const data = await sock.authState.keys.get('tctoken', [storageJid]);
    const token = data?.[storageJid]?.token;
    return Boolean(token?.length);
  } catch {
    return false;
  }
}

async function persistLidMapping(pnJid, lid) {
  if (!isLidJid(lid) || typeof sock.signalRepository?.lidMapping?.storeLIDPNMappings !== 'function') {
    return;
  }

  await sock.signalRepository.lidMapping.storeLIDPNMappings([{ pn: pnJid, lid }]);
}

async function assertNewChatAllowed() {
  if (typeof sock.fetchNewChatMessageCap !== 'function') {
    throw new Error('WhatsApp cannot verify new-chat limits on this device.');
  }

  try {
    const cap = await sock.fetchNewChatMessageCap();
    if (newChatCapBlocksSend(cap)) {
      throw new Error('WhatsApp has capped new chats on this linked device.');
    }
  } catch (err) {
    if (
      err?.message?.includes('capped new chats') ||
      err?.message?.includes('cannot verify new-chat')
    ) {
      throw err;
    }
    throw new Error('WhatsApp cannot verify new-chat limits on this device.');
  }

  if (typeof sock.fetchAccountReachoutTimelock !== 'function') {
    throw new Error('WhatsApp cannot verify new-chat limits on this device.');
  }

  try {
    const lock = await sock.fetchAccountReachoutTimelock();
    if (lock?.isActive) {
      throw new Error('WhatsApp is temporarily blocking new chats on this linked device.');
    }
  } catch (err) {
    if (
      err?.message?.includes('blocking new chats') ||
      err?.message?.includes('cannot verify new-chat')
    ) {
      throw err;
    }
    throw new Error('WhatsApp cannot verify new-chat limits on this device.');
  }
}

async function issueAndStoreTrustedContactToken(storageJid, pnJid) {
  if (typeof sock.issuePrivacyTokens !== 'function') {
    logger.warn('Privacy token API missing');
    return false;
  }

  const issueToLid = sock.serverProps?.lidTrustedTokenIssueToLid === true;
  const issueJid = pickIssuanceJid({ sendJid: storageJid, pnJid, issueToLid });
  const result = await sock.issuePrivacyTokens([issueJid]);
  const extracted = extractTrustedContactToken(result);
  if (!extracted) {
    logger.warn('Privacy IQ returned no trusted-contact token');
    return false;
  }

  await sock.authState.keys.set({
    tctoken: {
      [storageJid]: {
        token: Buffer.from(extracted.token),
        timestamp: extracted.timestamp,
      },
    },
  });
  logger.info('Stored trusted-contact token');
  return true;
}

async function prepareNewChatIfNeeded(sendJid, pnJid, needsNewChatHandshake, cachedLid) {
  if (!needsNewChatHandshake) {
    return;
  }

  if (!cachedLid) {
    await assertNewChatAllowed();
  }
  if (!(await hasUsableTcToken(sendJid))) {
    await issueAndStoreTrustedContactToken(sendJid, pnJid);
  }
  await persistLidMapping(pnJid, sendJid);
}

async function deliverContent(fullNumber, content) {
  if (!sock || !connected) {
    const err = new Error('WhatsApp not connected');
    err.code = 'NOT_CONNECTED';
    throw err;
  }

  const { sendJid, pnJid, cachedLid, needsNewChatHandshake } = await resolveSendJid(fullNumber);
  await prepareNewChatIfNeeded(sendJid, pnJid, needsNewChatHandshake, cachedLid);
  const refreshed = await prepareRecipient(sendJid);

  const result = await sock.sendMessage(sendJid, content);
  rememberMessage(result?.key, content);

  // First send after a session rebuild is often a prekey message that the phone
  // cannot decrypt. Wait for the incoming prekey, then send once more.
  if (refreshed) {
    await sleep(2500);
    const settled = await sock.sendMessage(sendJid, content);
    rememberMessage(settled?.key, content);
    logger.info('Sent follow-up after crypto refresh');
    return {
      ok: true,
      to: fullNumber,
      messageId: settled?.key?.id || result?.key?.id || null,
    };
  }

  return {
    ok: true,
    to: fullNumber,
    messageId: result?.key?.id || null,
  };
}

/**
 * Drop desynced Signal sessions, then fetch fresh prekeys for every device.
 * Open-but-stale sessions survive assertSessions(force) on the user JID only.
 */
async function refreshRecipientCrypto(jid) {
  const user = pnUserPart(jid);
  if (!user || refreshedCryptoUsers.has(user)) {
    return false;
  }

  let jids = [jid];
  try {
    if (typeof sock.getUSyncDevices === 'function') {
      const devices = await sock.getUSyncDevices([jid], false, false);
      const deviceJids = (devices || []).map((device) => device?.jid).filter(Boolean);
      if (deviceJids.length) {
        jids = deviceJids;
      }
    }
  } catch (err) {
    logger.warn({ err: err?.message }, 'Device list for session refresh failed');
  }

  try {
    if (typeof sock.signalRepository?.deleteSession === 'function') {
      await sock.signalRepository.deleteSession(jids);
    }
    if (typeof sock.assertSessions === 'function') {
      await sock.assertSessions(jids, true);
    }
  } catch (err) {
    logger.warn({ err: err?.message }, 'Recipient crypto refresh failed');
    throw new Error('WhatsApp could not rebuild the chat session.');
  }

  refreshedCryptoUsers.add(user);
  logger.info({ devices: jids.length }, 'Refreshed recipient crypto');
  return true;
}

/**
 * Warm crypto session with the already-resolved send JID.
 */
async function prepareRecipient(jid) {
  if (!sock) return false;

  const refreshed = await refreshRecipientCrypto(jid);

  try {
    if (typeof sock.presenceSubscribe === 'function') {
      await sock.presenceSubscribe(jid);
    }
  } catch {
    // optional
  }

  await sleep(refreshed ? 800 : 400);
  return refreshed;
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
  refreshedCryptoUsers.clear();
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
  return enqueueSend(() => deliverContent(fullNumber, { text: String(message) }));
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

    return deliverContent(fullNumber, {
      image: Buffer.from(raw, 'base64'),
      caption: caption ? String(caption) : undefined,
      mimetype: mimeType || 'image/png',
    });
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
