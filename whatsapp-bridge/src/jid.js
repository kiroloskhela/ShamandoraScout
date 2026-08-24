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

export function normalizeLid(raw) {
  if (typeof raw !== 'string' || raw.trim() === '') {
    return null;
  }

  const value = raw.trim();
  if (value.includes('@lid')) {
    return value;
  }

  return `${value}@lid`;
}

export function pnUserPart(jid) {
  return String(jid || '').split('@')[0]?.split(':')[0] || '';
}

export function usyncRowMatchesPn(row, pnJid) {
  const want = pnUserPart(pnJid);
  const got = pnUserPart(row?.id || '');
  return want !== '' && got === want;
}

export function pickIssuanceJid({ sendJid, pnJid, issueToLid }) {
  if (issueToLid && isLidJid(sendJid)) {
    return sendJid;
  }

  return pnJid;
}

export function newChatCapBlocksSend(cap) {
  return cap?.capping_status === 'CAPPED';
}

export function extractTrustedContactToken(result) {
  if (!result || typeof result !== 'object') {
    return null;
  }

  if (result.tag === 'tokens' && Array.isArray(result.content)) {
    return tokenFromNodes(result.content);
  }

  const content = result.content;
  if (!Array.isArray(content)) {
    return null;
  }

  const tokensNode = content.find((node) => node?.tag === 'tokens');
  if (tokensNode && Array.isArray(tokensNode.content)) {
    const found = tokenFromNodes(tokensNode.content);
    if (found) {
      return found;
    }
  }

  for (const child of content) {
    const nested = extractTrustedContactToken(child);
    if (nested) {
      return nested;
    }
  }

  return null;
}

function tokenFromNodes(nodes) {
  for (const tokenNode of nodes) {
    if (tokenNode?.tag !== 'token' || tokenNode.attrs?.type !== 'trusted_contact') {
      continue;
    }
    const raw = tokenNode.content;
    const usable = (raw instanceof Uint8Array || Buffer.isBuffer(raw)) && raw.length > 0;
    if (!tokenNode.attrs?.t || !usable) {
      continue;
    }

    return { token: raw, timestamp: tokenNode.attrs.t };
  }

  return null;
}

/**
 * @param {{ exists?: boolean, lid?: string|null, jid?: string|null }} lookup
 * @param {string} _fallbackPnJid unused; PN fallback is a ghost send
 */
export function pickSendJid(lookup, _fallbackPnJid) {
  if (lookup?.exists === false) {
    throw new Error('WhatsApp number does not exist');
  }

  if (isLidJid(lookup?.lid)) {
    return lookup.lid;
  }

  if (isLidJid(lookup?.jid)) {
    return lookup.jid;
  }

  throw new Error('WhatsApp identity mapping missing for this number');
}
