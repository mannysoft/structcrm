---
title: Queues, scheduler and cache
sidebar_position: 2
description: Run workers and the scheduler, and understand what breaks when you do not.
---

# Queues, scheduler and cache

## What is queued

| Job / notification | Trigger |
| :--- | :--- |
| `StaffInvitation` | Inviting a team member |
| `ClientInvitation` | Inviting a client to the portal |
| `SyncResendContactJob` | Registration with marketing consent and Resend configured |
| Marketing campaign emails | Sent directly by `marketing:send-due-emails` (not queued) |

Queued mail is the largest source of "it says sent but nothing arrived".

## Worker

Development:

```bash
php artisan queue:listen --tries=1 --timeout=0
```

Production (Supervisor or your platform's worker feature):

```ini
[program:structcrm-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/structcrm/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/structcrm/storage/logs/worker.log
stopwaitsecs=3600
```

Deploy restart: run `php artisan queue:restart` after each release so workers pick up new
code.

## Failed jobs

The `failed_jobs` table stores failures.

```bash
php artisan queue:failed                 # list
php artisan queue:retry all              # retry everything
php artisan queue:forget 5               # forget one
php artisan queue:flush                  # clear
```

Most failures are mail transport errors (bad credentials, unverified sender domain). Fix the
cause before retrying, or you will just fill the table again.

## Scheduler

One entry in `routes/console.php`:

```php
Schedule::command(SendDueMarketingEmails::class)
    ->dailyAt('09:00')
    ->withoutOverlapping();
```

Enable it with cron:

```cron
* * * * * cd /var/www/structcrm && php artisan schedule:run >> /dev/null 2>&1
```

Locally use `php artisan schedule:work`. Without the cron entry, campaigns never send.

## Cache

| Store | Use |
| :--- | :--- |
| `database` (default) | Config/route caches are files; the store backs `Cache::` calls |
| `file` | Simpler, single-server |
| `redis` | Recommended at scale, needs a server |

StructCRM caches little: permissions (spatie), the compiled config/routes/views, and any
`Cache::remember()` you add. `php artisan optimize:clear` clears all of it.

## Sessions

Sessions use the database driver. The `sessions` table grows until Laravel's lottery clears
it (`session.lottery`). On high-traffic deployments consider Redis or a scheduled
`php artisan session:clear` if you cannot tolerate the retention.

## A working production topology

```text
web (php-fpm) ── database ── queue worker(s)
                   └─ scheduler (cron)
```

No Redis, no Horizon, no separate cache server required. Add them when you need them.
