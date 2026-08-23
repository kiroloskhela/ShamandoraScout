import assert from 'node:assert/strict';
import test from 'node:test';
import {
  credsLookRegistered,
  isPairingStatus,
  nextLogoutAction,
  reconnectDelayMs,
  shouldWatchdogReconnect,
  STATUS_CONNECTION_REPLACED,
  STATUS_LOGGED_OUT,
  STATUS_RESTART_REQUIRED,
} from './reconnectPolicy.js';

test('registered creds need me.id or registered=true', () => {
  assert.equal(credsLookRegistered(null), false);
  assert.equal(credsLookRegistered({}), false);
  assert.equal(credsLookRegistered({ me: { id: '123@s.whatsapp.net' } }), true);
  assert.equal(credsLookRegistered({ registered: true }), true);
});

test('only 401/403 require pairing; unknown codes reuse the session', () => {
  assert.equal(isPairingStatus(STATUS_LOGGED_OUT), true);
  assert.equal(isPairingStatus(403), true);
  assert.equal(isPairingStatus(408), false);
  assert.equal(isPairingStatus(428), false);
  assert.equal(isPairingStatus(STATUS_CONNECTION_REPLACED), false);
  assert.equal(isPairingStatus(STATUS_RESTART_REQUIRED), false);
  assert.equal(isPairingStatus(undefined), false);
});

test('first logout retries saved session; second asks for pairing', () => {
  assert.equal(nextLogoutAction(1), 'retry_saved_session');
  assert.equal(nextLogoutAction(2), 'pairing_required');
});

test('watchdog stays quiet when pairing is required or already busy', () => {
  assert.equal(shouldWatchdogReconnect({
    connected: false,
    hasReusableSession: true,
    pairingRequired: false,
    busy: false,
  }), true);

  assert.equal(shouldWatchdogReconnect({
    connected: true,
    hasReusableSession: true,
    pairingRequired: false,
    busy: false,
  }), false);

  assert.equal(shouldWatchdogReconnect({
    connected: false,
    hasReusableSession: true,
    pairingRequired: true,
    busy: false,
  }), false);

  assert.equal(shouldWatchdogReconnect({
    connected: false,
    hasReusableSession: true,
    pairingRequired: false,
    busy: true,
  }), false);

  assert.equal(shouldWatchdogReconnect({
    connected: false,
    hasReusableSession: false,
    pairingRequired: false,
    busy: false,
  }), false);
});

test('440 waits longer than 515', () => {
  assert.equal(reconnectDelayMs(STATUS_RESTART_REQUIRED, 3), 1_000);
  assert.equal(reconnectDelayMs(STATUS_CONNECTION_REPLACED, 1), 15_000);
  assert.equal(reconnectDelayMs(408, 1), 2_000);
});
