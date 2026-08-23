/**
 * Reconnect rules for the Baileys bridge.
 * First 401/403 retries saved creds. Second 401/403 requires a new QR
 * (whatsapp.js then clears auth_session so Baileys can emit one).
 */
export const STATUS_LOGGED_OUT = 401;
export const STATUS_FORBIDDEN = 403;
export const STATUS_CONNECTION_REPLACED = 440;
export const STATUS_RESTART_REQUIRED = 515;

export function credsLookRegistered(creds) {
  if (!creds || typeof creds !== 'object') {
    return false;
  }

  return Boolean(creds.me?.id) || creds.registered === true;
}

export function isPairingStatus(statusCode) {
  const code = Number(statusCode);

  return code === STATUS_LOGGED_OUT || code === STATUS_FORBIDDEN;
}

export function nextLogoutAction(consecutiveLoggedOut) {
  if (consecutiveLoggedOut <= 1) {
    return 'retry_saved_session';
  }

  return 'pairing_required';
}

export function reconnectDelayMs(statusCode, attempt) {
  const code = Number(statusCode);
  if (code === STATUS_RESTART_REQUIRED) {
    return 1_000;
  }
  if (code === STATUS_CONNECTION_REPLACED) {
    return 15_000;
  }

  return Math.min(30_000, 1000 * 2 ** Math.min(Math.max(Number(attempt) || 1, 1), 5));
}

export function shouldWatchdogReconnect({
  connected,
  hasReusableSession,
  pairingRequired,
  busy,
}) {
  return Boolean(hasReusableSession)
    && !connected
    && !pairingRequired
    && !busy;
}
