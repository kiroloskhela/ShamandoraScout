# Redis (Wave 5)

Laravel uses `predis/predis` (`REDIS_CLIENT=predis`) so the VPS does not need the `phpredis` PHP extension.

## Recommended production drivers

Keep the queue on the database worker you already run via systemd. Use Redis for cache and session:

```env
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_DB=0
REDIS_CACHE_DB=1

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=database
```

Optional later: `QUEUE_CONNECTION=redis` and point `laravel-queue.service` at the same Redis instance.

## VPS one-time setup

```bash
apt-get update
apt-get install -y redis-server
systemctl enable --now redis-server
redis-cli ping   # PONG
```

Bind Redis to localhost only (default on Ubuntu). Do not expose `6379` publicly.

Then edit `/var/www/shamandora/.env` as above and:

```bash
cd /var/www/shamandora
php artisan config:cache
systemctl restart php8.3-fpm laravel-queue.service
```

## Verify

```bash
php artisan tinker --execute="Cache::put('redis_ok', 1, 60); echo Cache::get('redis_ok');"
```

Deploy does **not** flip drivers automatically — set `.env` after Redis is installed so a missing Redis cannot take the site down.
