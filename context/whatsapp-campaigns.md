# WhatsApp campaigns (Phase 1)

SuperAdmin bulk messaging via the existing Baileys bridge (`WhatsAppBridgeClient`).

## Routes

| Method | Path | Name |
|--------|------|------|
| GET | `/whatsapp/campaigns` | `whatsapp.campaigns.index` |
| GET | `/whatsapp/campaigns/create` | `whatsapp.campaigns.create` |
| POST | `/whatsapp/campaigns` | `whatsapp.campaigns.store` |
| GET | `/whatsapp/campaigns/{id}` | `whatsapp.campaigns.show` |
| POST | `/whatsapp/campaigns/{id}/confirm` | `whatsapp.campaigns.confirm` |
| POST | `/whatsapp/campaigns/{id}/pause\|resume\|cancel` | actions |

Auth: `checkAuth:SuperAdmin` only.

## Env

Reuses bridge settings (no new secrets in frontend):

```env
WHATSAPP_BRIDGE_URL=http://127.0.0.1:3010/send
WHATSAPP_BRIDGE_BASE_URL=http://127.0.0.1:3010
WHATSAPP_BRIDGE_TOKEN=<secret>
QUEUE_CONNECTION=database
```

A `queue:work` process must be running for paced sends.

## Defaults

- Delay between messages: 8–15 seconds (random)
- Max messages/hour: 60
- High-count confirm threshold: 100 recipients
- Personalization: `{name}` = FirstName + SecondName + ThirdName
- Consent: `PersonPhoneNumbers.WhatsAppConsent` default `1`; `DoNotContact` default `0`
- Blacklisted people and DNC / no-consent are excluded

## Out of scope (Phase 1)

- Scheduled start / allowed hours
- Delivered / read receipts (Baileys send ack only)
- AdminQetaa scoped campaigns

## Migrate

```bash
php artisan migrate
```
