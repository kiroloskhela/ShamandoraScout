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

Production installs `deploy/laravel-queue.service` on every deploy to `main`
(`systemctl enable --now laravel-queue.service`).

Local / manual:

```bash
php artisan queue:work database --sleep=1 --tries=3
```

Without a worker, jobs sit in the `jobs` table and mail/FCM/WhatsApp campaigns will not send.

Verify on the VPS:

```bash
systemctl status laravel-queue.service
php artisan queue:failed
```

PHPUnit keeps `QUEUE_CONNECTION=sync` so jobs run inline unless faked.

## Production `.env`

```env
QUEUE_CONNECTION=database
```

If this is still `sync`, queued jobs run inside the HTTP request (no worker needed, but slower).

## Jobs

| Job | Dispatched from |
|-----|-----------------|
| `App\Jobs\SendPasswordResetLinkMail` | `ForgotPasswordController::handle` |
| `App\Jobs\SendFcmNotification` | `NotificationController` (`send`, `sendToRoles`, `sendToUserId`, `sendToIds`) |
