import assert from 'node:assert/strict';
import test from 'node:test';
import {
  describePrivacyIq,
  extractTrustedContactToken,
  isLidJid,
  lidFromMappingRecord,
  newChatBlockedMessage,
  newChatCapBlocksSend,
  normalizeLid,
  pickIssuanceJid,
  pickSendJid,
  privacyIqError,
  toPnJid,
  usyncRowMatchesPn,
} from './jid.js';

test('toPnJid strips plus and local formatting', () => {
  assert.equal(toPnJid('+201000485402'), '201000485402@s.whatsapp.net');
  assert.equal(toPnJid('201000485402'), '201000485402@s.whatsapp.net');
  assert.throws(() => toPnJid(''), /Invalid phone number/);
});

test('pickSendJid prefers LID mapped from the same PN', () => {
  const pn = '201000485402@s.whatsapp.net';
  assert.equal(
    pickSendJid({ exists: true, lid: '1234567890@lid', jid: pn }, pn),
    '1234567890@lid'
  );
});

test('pickSendJid uses onWhatsApp jid when it is already a LID', () => {
  const pn = '201000485402@s.whatsapp.net';
  assert.equal(
    pickSendJid({ exists: true, lid: null, jid: '999@lid' }, pn),
    '999@lid'
  );
});

test('pickSendJid throws when the number exists but has no LID', () => {
  const pn = '201000485402@s.whatsapp.net';
  assert.throws(
    () => pickSendJid({ exists: true, jid: pn }, pn),
    /identity mapping missing/
  );
});

test('pickSendJid throws when identity is unknown and there is no LID', () => {
  const pn = '201000485402@s.whatsapp.net';
  assert.throws(
    () => pickSendJid({}, pn),
    /identity mapping missing/
  );
});

test('pickSendJid throws when WhatsApp says the number does not exist', () => {
  const pn = '201000485402@s.whatsapp.net';
  assert.throws(
    () => pickSendJid({ exists: false, jid: pn }, pn),
    /WhatsApp number does not exist/
  );
});

test('lidFromMappingRecord only reads the local store shape', () => {
  const pn = '201008021921@s.whatsapp.net';
  assert.equal(lidFromMappingRecord({ '201008021921': '1234567890' }, pn), '1234567890@lid');
  assert.equal(lidFromMappingRecord({ '201008021921': '1234567890@lid' }, pn), '1234567890@lid');
  assert.equal(lidFromMappingRecord({ '201000485402': '999' }, pn), null);
  assert.equal(lidFromMappingRecord({}, pn), null);
});

test('usyncRowMatchesPn binds the returned row to the requested PN', () => {
  const pn = '201008021921@s.whatsapp.net';
  assert.equal(usyncRowMatchesPn({ id: pn }, pn), true);
  assert.equal(usyncRowMatchesPn({ id: '201008021921:0@s.whatsapp.net' }, pn), true);
  assert.equal(usyncRowMatchesPn({ id: '201000485402@s.whatsapp.net' }, pn), false);
  assert.equal(usyncRowMatchesPn({ id: '999@lid' }, pn), false);
});

test('pickIssuanceJid issues to PN unless WhatsApp asks for LID', () => {
  const pn = '201008021921@s.whatsapp.net';
  const lid = '1234567890@lid';
  assert.equal(pickIssuanceJid({ sendJid: lid, pnJid: pn, issueToLid: false }), pn);
  assert.equal(pickIssuanceJid({ sendJid: lid, pnJid: pn, issueToLid: true }), lid);
  assert.equal(pickIssuanceJid({ sendJid: pn, pnJid: pn, issueToLid: true }), pn);
});

test('newChatCapBlocksSend only when WhatsApp reports CAPPED', () => {
  assert.equal(newChatCapBlocksSend({ capping_status: 'CAPPED' }), true);
  assert.equal(newChatCapBlocksSend({ capping_status: 'NONE' }), false);
  assert.equal(newChatCapBlocksSend(null), false);
});

test('newChatBlockedMessage explains companion lock vs timed lock', () => {
  assert.equal(newChatBlockedMessage({ isActive: false }), null);
  assert.equal(
    newChatBlockedMessage({ isActive: true, enforcementType: 'WEB_COMPANION_ONLY' }),
    'WhatsApp only allows this linked device to message existing chats. Open the chat once on the phone that scanned the QR, then send again.'
  );
  assert.equal(
    newChatBlockedMessage({
      isActive: true,
      timeEnforcementEnds: new Date('2026-08-25T12:00:00.000Z'),
    }),
    'WhatsApp is temporarily blocking new chats on this linked device until 2026-08-25T12:00:00.000Z.'
  );
});

test('extractTrustedContactToken reads bytes from a privacy IQ result', () => {
  const token = Buffer.from('tok');
  assert.deepEqual(
    extractTrustedContactToken({
      content: [
        {
          tag: 'tokens',
          content: [
            {
              tag: 'token',
              attrs: { type: 'trusted_contact', t: '1700000000' },
              content: token,
            },
          ],
        },
      ],
    }),
    { token, timestamp: '1700000000' }
  );
  assert.equal(extractTrustedContactToken({ content: [] }), null);
  const nestedToken = Buffer.from('nested');
  assert.deepEqual(
    extractTrustedContactToken({
      tag: 'iq',
      content: [
        {
          tag: 'privacy',
          content: [
            {
              tag: 'tokens',
              content: [
                {
                  tag: 'token',
                  attrs: { type: 'trusted_contact', t: '1700000001' },
                  content: nestedToken,
                },
              ],
            },
          ],
        },
      ],
    }),
    { token: nestedToken, timestamp: '1700000001' }
  );
});

test('extractTrustedContactToken accepts Uint8Array and integer arrays', () => {
  const bytes = Uint8Array.from([1, 2, 3]);
  const extracted = extractTrustedContactToken({
    content: [
      {
        tag: 'tokens',
        content: [
          {
            tag: 'token',
            attrs: { type: 'trusted_contact', t: '1700000002' },
            content: bytes,
          },
        ],
      },
    ],
  });
  assert.equal(Buffer.compare(extracted.token, Buffer.from(bytes)), 0);
  assert.equal(extracted.timestamp, '1700000002');

  const fromArray = extractTrustedContactToken({
    content: [
      {
        tag: 'tokens',
        content: [
          {
            tag: 'token',
            attrs: { t: '1700000003' },
            content: [9, 8, 7],
          },
        ],
      },
    ],
  });
  assert.deepEqual([...fromArray.token], [9, 8, 7]);
});

test('privacyIqError and describePrivacyIq stay off the token bytes', () => {
  assert.deepEqual(
    privacyIqError({
      tag: 'iq',
      content: [{ tag: 'error', attrs: { code: '401', text: 'not-authorized' } }],
    }),
    { code: '401', text: 'not-authorized' }
  );
  const described = describePrivacyIq({
    tag: 'iq',
    content: [
      {
        tag: 'tokens',
        content: [
          {
            tag: 'token',
            attrs: { type: 'trusted_contact', t: '1' },
            content: Buffer.from('x'),
          },
        ],
      },
    ],
  });
  assert.equal(described.tokenCount, 1);
  assert.equal(described.tokens[0].contentLen, 1);
  assert.equal(described.tokens[0].hasT, true);
  assert.equal(described.error, null);
});

test('normalizeLid appends @lid when WhatsApp returns a bare id', () => {
  assert.equal(normalizeLid('1234567890@lid'), '1234567890@lid');
  assert.equal(normalizeLid('1234567890'), '1234567890@lid');
  assert.equal(normalizeLid(''), null);
  assert.equal(normalizeLid(null), null);
  assert.equal(isLidJid('123@lid'), true);
  assert.equal(isLidJid('201000485402@s.whatsapp.net'), false);
});
