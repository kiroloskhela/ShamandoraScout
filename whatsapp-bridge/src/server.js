import { existsSync, readFileSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import express from 'express';
import pino from 'pino';
import { getStatus, sendText, startWhatsApp } from './whatsapp.js';

// Load sibling .env without a dotenv dependency (pm2 often omits env files).
const envPath = join(dirname(fileURLToPath(import.meta.url)), '..', '.env');
if (existsSync(envPath)) {
  for (const line of readFileSync(envPath, 'utf8').split('\n')) {
    const trimmed = line.trim();
    if (!trimmed || trimmed.startsWith('#')) {
      continue;
    }
    const eq = trimmed.indexOf('=');
    if (eq <= 0) {
      continue;
    }
    const key = trimmed.slice(0, eq).trim();
    const value = trimmed.slice(eq + 1).trim();
    if (process.env[key] === undefined) {
      process.env[key] = value;
    }
  }
}

const logger = pino({ level: process.env.LOG_LEVEL || 'info' });
const PORT = Number(process.env.PORT || 3000);
const BRIDGE_TOKEN = process.env.BRIDGE_TOKEN || '';

const app = express();
app.use(express.json({ limit: '64kb' }));

function requireToken(req, res, next) {
  if (!BRIDGE_TOKEN) {
    return res.status(500).json({
      ok: false,
      error: 'BRIDGE_TOKEN is not configured on the bridge',
    });
  }

  const header = req.get('X-Bridge-Token') || '';
  if (header !== BRIDGE_TOKEN) {
    return res.status(401).json({ ok: false, error: 'Unauthorized' });
  }

  return next();
}

app.get('/health', (_req, res) => {
  const status = getStatus();
  res.json({ ok: status.ok, connected: status.connected });
});

app.get('/qr', (_req, res) => {
  const status = getStatus();
  res.json({
    ok: status.ok,
    connected: status.connected,
    qr: status.qr,
  });
});

app.post('/send', requireToken, async (req, res) => {
  const fullNumber = req.body?.full_number;
  const message = req.body?.message;

  if (!fullNumber || !message) {
    return res.status(422).json({
      ok: false,
      error: 'full_number and message are required',
    });
  }

  try {
    const result = await sendText(fullNumber, message);
    return res.json(result);
  } catch (err) {
    const code = err?.code === 'NOT_CONNECTED' ? 503 : 500;
    logger.error({ err }, 'Send failed');
    return res.status(code).json({
      ok: false,
      error: err?.message || 'Send failed',
    });
  }
});

app.listen(PORT, '127.0.0.1', async () => {
  logger.info({ PORT }, 'WhatsApp bridge listening on 127.0.0.1');
  if (!BRIDGE_TOKEN) {
    logger.warn('BRIDGE_TOKEN is empty — /send will reject all requests');
  }
  await startWhatsApp();
});
