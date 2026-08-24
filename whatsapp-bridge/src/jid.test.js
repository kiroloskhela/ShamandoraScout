import assert from 'node:assert/strict';
import test from 'node:test';
import {
  extractTrustedContactToken,
  isLidJid,
  newChatCapBlocksSend,
  normalizeLid,
  pickIssuanceJid,
  pickSendJid,
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

test('normalizeLid appends @lid when WhatsApp returns a bare id', () => {
  assert.equal(normalizeLid('1234567890@lid'), '1234567890@lid');
  assert.equal(normalizeLid('1234567890'), '1234567890@lid');
  assert.equal(normalizeLid(''), null);
  assert.equal(normalizeLid(null), null);
  assert.equal(isLidJid('123@lid'), true);
  assert.equal(isLidJid('201000485402@s.whatsapp.net'), false);
});
