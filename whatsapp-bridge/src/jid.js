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

export function lidFromMappingRecord(record, pnJid) {
  const user = pnUserPart(pnJid);
  const lidUser = record?.[user];
  if (typeof lidUser !== 'string' || lidUser === '') {
    return null;
  }

  return normalizeLid(lidUser.includes('@') ? lidUser : `${lidUser}@lid`);
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

export function newChatBlockedMessage(lock) {
  if (!lock?.isActive) {
    return null;
  }

  const type = String(lock.enforcementType || 'DEFAULT');
  let ends = null;
  if (lock.timeEnforcementEnds instanceof Date && !Number.isNaN(lock.timeEnforcementEnds.valueOf())) {
    ends = lock.timeEnforcementEnds.toISOString();
  } else if (typeof lock.timeEnforcementEnds === 'string' && lock.timeEnforcementEnds !== '') {
    ends = lock.timeEnforcementEnds;
  }

  if (type === 'WEB_COMPANION_ONLY') {
    return 'WhatsApp only allows this linked device to message existing chats. Open the chat once on the phone that scanned the QR, then send again.';
  }
  if (ends) {
    return `WhatsApp is temporarily blocking new chats on this linked device until ${ends}.`;
  }
  return 'WhatsApp is temporarily blocking new chats on this linked device.';
}

export function privacyIqError(result) {
  const nodes = [];
  if (result?.tag === 'error') {
    nodes.push(result);
  }
  if (Array.isArray(result?.content)) {
    nodes.push(...result.content.filter((node) => node?.tag === 'error'));
  }
  const err = nodes[0];
  if (!err) {
    return null;
  }

  return {
    code: err.attrs?.code ?? null,
    text: err.attrs?.text ?? null,
  };
}

export function describePrivacyIq(result) {
  if (!result || typeof result !== 'object') {
    return { empty: true };
  }

  const content = Array.isArray(result.content) ? result.content : [];
  const tokensNode = content.find((node) => node?.tag === 'tokens');
  const tokenNodes = Array.isArray(tokensNode?.content) ? tokensNode.content : [];

  return {
    tag: result.tag ?? null,
    childTags: content.map((node) => node?.tag).filter(Boolean),
    contentIsArray: Array.isArray(result.content),
    contentType: result.content == null ? 'null' : result.content.constructor?.name || typeof result.content,
    tokenCount: tokenNodes.length,
    tokensContentType:
      tokensNode?.content == null
        ? 'null'
        : tokensNode.content.constructor?.name || typeof tokensNode.content,
    tokens: tokenNodes.map((node) => ({
      tag: node?.tag ?? null,
      type: node?.attrs?.type ?? null,
      attrKeys: Object.keys(node?.attrs || {}),
      hasT: Boolean(node?.attrs?.t),
      contentType: node?.content == null ? 'null' : node.content.constructor?.name || typeof node.content,
      contentLen: node?.content?.length ?? 0,
    })),
    error: privacyIqError(result),
  };
}

export function extractTrustedContactToken(result) {
  if (!result || typeof result !== 'object') {
    return null;
  }

  if (result.tag === 'tokens') {
    const found = tokenFromNode(result);
    if (found) {
      return found;
    }
  }

  const content = result.content;
  if (!Array.isArray(content)) {
    return null;
  }

  const tokensNode = content.find((node) => node?.tag === 'tokens');
  if (tokensNode) {
    const found = tokenFromNode(tokensNode);
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

function tokenFromNode(node) {
  if (node?.tag === 'token') {
    return tokenFromAttrs(node);
  }
  if (!Array.isArray(node?.content)) {
    return null;
  }
  for (const child of node.content) {
    const found = tokenFromAttrs(child);
    if (found) {
      return found;
    }
  }
  return null;
}

function coerceTokenBytes(raw) {
  if (raw instanceof Uint8Array || Buffer.isBuffer(raw)) {
    return raw.length > 0 ? Buffer.from(raw) : null;
  }
  if (typeof raw === 'string' && raw.length > 0) {
    return Buffer.from(raw, 'base64');
  }
  if (Array.isArray(raw) && raw.length > 0 && raw.every((n) => Number.isInteger(n))) {
    return Buffer.from(raw);
  }
  return null;
}

function tokenFromAttrs(tokenNode) {
  if (tokenNode?.tag !== 'token') {
    return null;
  }
  const type = tokenNode.attrs?.type;
  if (type && type !== 'trusted_contact') {
    return null;
  }
  const bytes = coerceTokenBytes(tokenNode.content);
  const timestamp = tokenNode.attrs?.t;
  if (!timestamp || !bytes) {
    return null;
  }
  return { token: bytes, timestamp };
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
