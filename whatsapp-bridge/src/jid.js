/**
 * Phone-number JID vs WhatsApp LID addressing.
 * WhatsApp now encrypts 1:1 chats against @lid. Sending only to
 * digits@s.whatsapp.net can return a messageId while the phone never decrypts it.
 */

export function toPnJid(fullNumber) {
  const digits = String(fullNumber || '').replace(/\D+/g, '');
  if (!digits) {
    throw new Error('Invalid phone number');
  }

  return `${digits}@s.whatsapp.net`;
}

export function isLidJid(value) {
  return typeof value === 'string' && value.includes('@lid');
}

/**
 * @param {{ exists?: boolean, lid?: string|null, jid?: string|null }} lookup
 * @param {string} fallbackPnJid
 */
export function pickSendJid(lookup, fallbackPnJid) {
  if (lookup?.exists === false) {
    throw new Error('WhatsApp number does not exist');
  }

  if (isLidJid(lookup?.lid)) {
    return lookup.lid;
  }

  if (isLidJid(lookup?.jid)) {
    return lookup.jid;
  }

  return fallbackPnJid;
}
