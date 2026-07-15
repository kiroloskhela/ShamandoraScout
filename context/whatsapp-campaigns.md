# WhatsApp campaigns (Phase 1 + CSV)

SuperAdmin bulk messaging via the existing Baileys bridge (`WhatsAppBridgeClient`).

## Routes

| Method | Path | Name |
|--------|------|------|
| GET | `/whatsapp/campaigns` | `whatsapp.campaigns.index` |
| GET | `/whatsapp/campaigns/create` | `whatsapp.campaigns.create` |
| GET | `/whatsapp/campaigns/create-csv` | `whatsapp.campaigns.create-csv` |
| GET | `/whatsapp/campaigns/csv-template` | `whatsapp.campaigns.csv-template` |
| POST | `/whatsapp/campaigns/csv` | `whatsapp.campaigns.store-csv` |
| POST | `/whatsapp/campaigns` | `whatsapp.campaigns.store` |
| GET | `/whatsapp/campaigns/{id}` | `whatsapp.campaigns.show` |
| POST | `/whatsapp/campaigns/{id}/confirm` | `whatsapp.campaigns.confirm` |
| POST | `/whatsapp/campaigns/{id}/pause\|resume\|cancel` | actions |

Auth: `checkAuth:SuperAdmin` only.

## Two create modes

1. **Directory campaign** — pick people from the DB, one template with `{name}`.
2. **CSV campaign** — upload `Phone Number,Message`; each row gets its own message. Phones are normalized to Egypt `+20…` (e.g. `1000485402` → `+201000485402`). Max 2000 rows. Pause / resume / cancel work the same as directory campaigns.

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
- Personalization (directory mode): `{name}` = FirstName + SecondName + ThirdName
- Consent: `PersonPhoneNumbers.WhatsAppConsent` default `1`; `DoNotContact` default `0`
- Blacklisted people and DNC / no-consent are excluded (directory mode)

## Out of scope

- Scheduled start / allowed hours
- Delivered / read receipts (Baileys send ack only)
- AdminQetaa scoped campaigns

## Migrate

```bash
php artisan migrate
```

Includes `2026_07_15_500001_whatsapp_campaign_recipients_csv_support` (nullable `person_id`, unique per campaign phone).
