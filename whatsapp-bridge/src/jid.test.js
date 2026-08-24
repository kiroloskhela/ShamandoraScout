import assert from 'node:assert/strict';
import test from 'node:test';
import { isLidJid, pickSendJid, toPnJid } from './jid.js';

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

test('pickSendJid falls back to PN when no LID mapping exists', () => {
  const pn = '201000485402@s.whatsapp.net';
  assert.equal(pickSendJid({ exists: true, jid: pn }, pn), pn);
  assert.equal(pickSendJid({}, pn), pn);
});

test('pickSendJid throws when WhatsApp says the number does not exist', () => {
  const pn = '201000485402@s.whatsapp.net';
  assert.throws(
    () => pickSendJid({ exists: false, jid: pn }, pn),
    /WhatsApp number does not exist/
  );
});

test('isLidJid only matches @lid', () => {
  assert.equal(isLidJid('123@lid'), true);
  assert.equal(isLidJid('201000485402@s.whatsapp.net'), false);
  assert.equal(isLidJid(null), false);
});
