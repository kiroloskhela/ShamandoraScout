# Shamandora WhatsApp Bridge (Baileys + LocalAuth)

HTTP API for Laravel (`WhatsAppBridgeController`). Session files live in `auth_session/` and survive process restarts — scan the QR **once** on first launch.

## Setup (VPS)

```bash
cd whatsapp-bridge
cp .env.example .env
# set BRIDGE_TOKEN to the same value as Laravel WHATSAPP_BRIDGE_TOKEN
npm install
npm start
```

Or with pm2:

```bash
pm2 start src/server.js --name shamandora-wa --cwd /path/to/ShamandoraScout/whatsapp-bridge
pm2 save
```

## Laravel env

```env
WHATSAPP_BRIDGE_URL=http://127.0.0.1:3000/send
WHATSAPP_BRIDGE_TOKEN=<same as BRIDGE_TOKEN>
```

Optional base URL for the SuperAdmin status page (health + QR):

```env
WHATSAPP_BRIDGE_BASE_URL=http://127.0.0.1:3000
```

If unset, Laravel derives the base from `WHATSAPP_BRIDGE_URL` by stripping a trailing `/send`.

## First pairing

1. Start the bridge.
2. Open SuperAdmin → واتساب (`/whatsapp/status`) or watch the terminal QR.
3. Scan with WhatsApp → Linked devices.
4. Confirm `/health` shows `"connected": true`.

**Do not delete `auth_session/`** for normal restarts. The process keeps the socket alive (ping + presence) and reconnects from disk. If WhatsApp unlinks the device, the bridge retries the saved session once, then clears `auth_session` so a new QR can appear.

After a **Baileys major bump** (v6 → v7), stop the process, wipe `auth_session/`, `npm install`, start, then scan a **new** QR. Old session files will not decrypt messages on phones even if `/health` says connected.

## Endpoints

| Method | Path | Auth | Response |
|--------|------|------|----------|
| GET | `/health` | none | `{ ok, connected, hasReusableSession, pairingRequired, reconnecting, lastDisconnectCode }` |
| GET | `/qr` | `X-Bridge-Token` | `{ ok, connected, qr: null\|dataUrl }` |
| POST | `/reconnect` | `X-Bridge-Token` | `{ ok: true, ...status }` — reuse saved session (no QR in the body) |
| POST | `/send` | `X-Bridge-Token` | `{ ok: true, to, messageId }` |

Bind is `127.0.0.1` only. Do not expose port 3000 publicly without a firewall.

## systemd (optional)

```ini
[Unit]
Description=Shamandora WhatsApp Bridge
After=network.target

[Service]
Type=simple
WorkingDirectory=/path/to/ShamandoraScout/whatsapp-bridge
EnvironmentFile=/path/to/ShamandoraScout/whatsapp-bridge/.env
ExecStart=/usr/bin/node src/server.js
Restart=always
RestartSec=5
User=www-data

[Install]
WantedBy=multi-user.target
```
