# Async queue (Wave 4 — Package D)

Outbound Brevo password-reset mail and FCM pushes run as queued jobs so HTTP requests do not wait on external APIs.

## Config

Set in `.env` (and `.env.example` when present):

```env
QUEUE_CONNECTION=database
```

Migrate the `jobs` table (migration `2026_07_15_200001_create_jobs_table.php`):

```bash
php artisan migrate
```

## Worker

Run a worker in every environment that uses `database` (or Redis) queues:

```bash
php artisan queue:work
```

Without a worker, jobs sit in the `jobs` table and mail/FCM will not send.

PHPUnit keeps `QUEUE_CONNECTION=sync` so jobs run inline unless faked.

## Jobs

| Job | Dispatched from |
|-----|-----------------|
| `App\Jobs\SendPasswordResetLinkMail` | `ForgotPasswordController::handle` |
| `App\Jobs\SendFcmNotification` | `NotificationController` (`send`, `sendToRoles`, `sendToUserId`, `sendToIds`) |
