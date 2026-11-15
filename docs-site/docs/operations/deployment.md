---
title: Deployment
sidebar_position: 5
description: Ship StructCRM to Laravel Cloud or a plain Linux server.
---

# Deployment

## Requirements recap

- PHP 8.4 with `mbstring`, `dom`, `fileinfo`, `sqlite3`/`pdo_sqlite`, `bcmath`
- One database (MySQL recommended)
- Node 20+ available at build time
- A queue worker and the scheduler

## Option A — Laravel Cloud

1. Connect the repository and create an environment.
2. Build command: `composer install --no-dev --optimize-autoloader && npm ci && npm run build`
3. Deploy command: `php artisan migrate --force`
4. Add a **worker** (`php artisan queue:work --tries=3`) and enable the **scheduler**.
5. Set environment variables (see [Environment variables](/docs/operations/environment-variables)).
6. Attach a database and a writable storage volume.

Cloud handles TLS and the queue/scheduler integration, so no cron or Supervisor config is
needed.

## Option B — Linux server (Nginx + PHP-FPM)

```bash
# 1. Code
git clone https://github.com/mannysoft/structcrm.git /var/www/structcrm
cd /var/www/structcrm

# 2. Dependencies and build
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 3. Environment
cp .env.example .env
php artisan key:generate
# edit .env: APP_ENV=production, APP_DEBUG=false, APP_URL, DB_*, MAIL_*

# 4. Database
php artisan migrate --force

# 5. Cache and links
php artisan optimize
php artisan storage:link      # only if FILESYSTEM_DISK=public

# 6. Permissions
chown -R www-data:www-data storage bootstrap/cache
```

Nginx virtual host:

```nginx
server {
    listen 443 ssl http2;
    server_name crm.example.com;
    root /var/www/structcrm/public;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Then add the Supervisor worker and the scheduler cron from
[Queues, scheduler and cache](/docs/operations/queues-and-cache).

## Every-deploy routine

```bash
php artisan down --retry=60          # optional maintenance window
git pull --ff-only
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize                # config/route/view/event cache
php artisan queue:restart
php artisan up
```

`queue:restart` is not optional: workers hold old code in memory.

## Post-deploy verification

| Check | Command |
| :--- | :--- |
| App boots | `curl -s -o /dev/null -w '%{http_code}' https://crm.example.com/up` → `200` |
| Routes registered | `php artisan route:list --except-vendor \| wc -l` |
| Assets built | Page loads with styles (no Vite manifest error) |
| Migrations current | `php artisan migrate:status` |
| Queue draining | `php artisan queue:monitor default:100` |
| Scheduler running | A campaign or job fires at the expected time |
| Mail works | Invite a test user and check delivery |

## Rollback

1. `php artisan down`
2. `git checkout <previous-tag>`
3. `composer install --no-dev --optimize-autoloader && npm ci && npm run build`
4. `php artisan migrate:rollback --step=1` **only if** the release added migrations
5. `php artisan optimize && php artisan queue:restart`
6. `php artisan up`

Keep the previous release's storage and database dump available — a code rollback does not
undo data writes.

## Security checklist

- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] HTTPS enforced; `APP_URL` uses `https://`
- [ ] `SESSION_SECURE_COOKIE=true` (or `SESSION_ENCRYPT=true`) behind TLS
- [ ] Database not exposed publicly
- [ ] Demo seeders **not** run
- [ ] Registration disabled if the instance is private
- [ ] Backups verified by an actual restore
