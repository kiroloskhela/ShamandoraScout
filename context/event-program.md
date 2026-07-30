# Event Program (camp leader missions)

Personalized daily programs for leaders on camp-style events (`معسكر مجمع`, `معسكر`, `يوم مجمع`).

## Operator flow

1. Open **Camp leader programs** (`/event-program`)
2. Open / create program for a SeasonEvent
3. **Download guide template** (xlsx) — or use CSV pack under `storage/app/templates/event_program_guide/`
4. Fill Meta + Day N matrices + Resources
5. **Import** via upload or public Google Sheets URL
6. Answer AI / rule-based clarifying questions if needed
7. Edit in UI → **Publish**
8. **WhatsApp draft** → confirm in WhatsApp campaigns UI

## Refresh (small mission updates)

1. First import with a **Google Sheets URL** (saved on the program), or paste the URL in program settings.
2. Edit missions/games in the sheet.
3. Click **Refresh from sheet** on `/event-program/{id}` (or Refresh on the list).
4. Already-matched leaders are remembered — usually one click, stays published.
5. Only brand-new unmatched names open the Q&A wizard.


```bash
php artisan event-program:import {seasonEventId} --file=/path/to/guide.xlsx --commit
php artisan event-program:import {seasonEventId} --url='https://docs.google.com/spreadsheets/d/.../edit' --answers='{"q1":"123"}' --commit
```

## Leader UI

- `/my-program` — list published programs assigned to me
- `/my-program/{seasonEventId}/day/{n}` — timeline + game/lecture links

## API (Sanctum)

- `GET /api/programs`
- `GET /api/programs/{seasonEventId}`

## Env

```env
GEMINI_API_KEY=
EVENT_PROGRAM_AI_MODEL=gemini-2.5-flash
```

If `GEMINI_API_KEY` is empty, import review still works with rule-based questions.
