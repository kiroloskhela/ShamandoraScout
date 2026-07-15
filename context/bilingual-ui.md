# Bilingual UI status

## 2026-07-15

### Done
- Locale infrastructure: `SetLocale`, `/locale/{locale}`, cookie + session (`ar`/`en`)
- Shell: `layouts/app.blade.php` + `login.blade.php` fully `__()`-based
- Shared `x-data-table` chrome (search/filter/actions/empty/pagination)
- Home dashboard, forgot-password, liveform public wizard + closed/finalize/waiting
- Liveform settings admin page
- Enrolments tables, waiting list, person show/index, profile, exam marks
- WhatsApp campaigns views
- Finance, booking finance, events, medicine (core), games, inventory-issue, attendance
- JSON catalogs: ~955 keys, en/ar parity
- Tests: `LocaleAndThemeShellTest` (login, dashboard, forgot-password, switch)

### Remaining (lower priority)
- Long-form CRUD blades still partially Arabic (district/manteqa/role/etc. legacy SB Admin pages)
- Some controller flash/validation messages (~Arabic in PHP)
- Full body copy on rarely used admin constants pages
- Calendar component may still force `ar` locale for FullCalendar

### How to use
Switch language from the header (ع / EN). Public `/liveform` and forgot-password respect the same session/cookie locale.
