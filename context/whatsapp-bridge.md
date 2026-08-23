# WhatsApp bridge (Baileys + LocalAuth)

In-repo Node service under `whatsapp-bridge/` that keeps a **persistent** WhatsApp Web session on disk so the QR is scanned **once** (unless `auth_session/` is deleted or WhatsApp logs the device out).

Laravel continues to call the same HTTP contract via `WhatsAppBridgeController` (`WHATSAPP_BRIDGE_URL` + `WHATSAPP_BRIDGE_TOKEN`).

## Layout

| Path | Role |
|------|------|
| `whatsapp-bridge/src/server.js` | Express: `/send`, `/health`, `/qr` |
| `whatsapp-bridge/src/whatsapp.js` | Baileys + `useMultiFileAuthState` |
| `whatsapp-bridge/auth_session/` | Creds (gitignored) — **back up; do not delete** |
| SuperAdmin UI | `GET /whatsapp/status` (`WhatsAppStatusController`) |

## Laravel env

```env
WHATSAPP_BRIDGE_URL=http://127.0.0.1:3000/send
WHATSAPP_BRIDGE_TOKEN=<long secret>
# optional; otherwise derived by stripping /send from the URL above
WHATSAPP_BRIDGE_BASE_URL=http://127.0.0.1:3000
```

Bridge `.env`:

```env
PORT=3000
BRIDGE_TOKEN=<same as WHATSAPP_BRIDGE_TOKEN>
```

## VPS

1. `cd whatsapp-bridge && cp .env.example .env && npm install`
2. `pm2 start src/server.js --name shamandora-wa` (or systemd — see `whatsapp-bridge/README.md`)
3. SuperAdmin → واتساب → scan QR on first boot
4. Firewall: keep port **3000 on localhost only**

Reconnect uses exponential backoff with saved creds; no re-scan after normal restarts.

The bridge keeps the socket alive (WebSocket ping + presence heartbeat) and reconnects from `auth_session/` when the connection drops. A Laravel schedule `whatsapp:wake` (every 2 minutes) asks the bridge to reconnect if it is down but still has a reusable session.

If WhatsApp **unlinks** the device from the phone (Linked devices → log out), the bridge retries the saved session **once**. A second 401/403 clears `auth_session` so a **new QR** can be issued. Transient drops do not require a scan.

## Tests

`tests/Feature/WhatsAppStatusControllerTest.php` — bridge down / connected / reconnect (`Http::fake`).
`tests/Feature/WakeWhatsAppSessionTest.php` — scheduled wake reconnects saved session, skips pairing and non-local URLs.

## Out of scope

- Official WhatsApp Cloud API
- Restoring WhatsApp on forgot-password (intentionally removed earlier)
- Auto-deploy of the Node process from Laravel deploy scripts
