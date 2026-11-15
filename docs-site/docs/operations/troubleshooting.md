---
title: Troubleshooting
sidebar_position: 6
description: Fast answers for the failures people actually hit.
---

# Troubleshooting

## "Unable to locate file in Vite manifest"

Assets were never built, or the manifest is stale.

```bash
npm run build        # or npm run dev while developing
```

## Styles are missing after a UI change

Same cause. The browser is loading the old manifest; rebuild or keep `npm run dev` running.

## `419 Page Expired`

CSRF token mismatch — usually a stale form after a session expiry, or `APP_KEY` changed. Hard
refresh the page; if it persists, check `SESSION_DRIVER` and that `APP_KEY` is stable across
deploys.

## `403` in the AI assistant

- No AI provider key configured → set `DEEPSEEK_API_KEY` (or another provider) and
  `php artisan config:clear`.
- Logged in as a **client portal user** → the assistant is staff-only.
- Provider rejected the request (quota/key) → check `storage/logs/laravel.log`.

## Invitations never arrive

1. Is a queue worker running? `php artisan queue:work` or check Supervisor.
2. Is `QUEUE_CONNECTION=sync` for development? Then mail is sent inline.
3. Is `MAIL_MAILER=log`? Then the email is only in `storage/logs/laravel.log`.
4. Is `APP_URL` correct? Signed links are built from it.
5. Check `failed_jobs` — `php artisan queue:failed`.

## Marketing emails never send

- Scheduler cron missing (`schedule:run`).
- No **active** campaign.
- No opted-in users whose consent is old enough for the step's `delay_days`.
- The user already received that step (deduplicated in `marketing_email_logs`).

## Client cannot see the portal

- Invitation never accepted (check `users.type = client`, `client_id` set).
- They are signing in at `/login` instead of `/portal/login` — the guards are separate.

## `Database is locked` (SQLite)

SQLite allows one writer. Run fewer concurrent workers, or move to MySQL. Never run
`queue:work` with many processes against SQLite in production.

## Migrations fail on a fresh database

```bash
php artisan migrate:fresh --seed    # local only, destroys data
```

If a migration fails in production, read the error: most are foreign keys created in the
wrong order or a column that already exists from a manual change.

## Uploads return 403 / images do not load

- With `FILESYSTEM_DISK=public`, run `php artisan storage:link`.
- Check `storage` permissions (`www-data` must be able to write).
- Behind a proxy, make sure `APP_URL` matches the public scheme/host, otherwise generated
  file URLs point at the wrong host.

## Changes do not appear in production

Cached config/routes/views:

```bash
php artisan optimize:clear
php artisan optimize
```

## Dusk tests time out

- Server not running against `database/dusk.sqlite`.
- Server started without `--no-reload` (Dusk swaps `.env` and restarts it).
- Chrome/chromedriver version mismatch: `php artisan dusk:chrome-driver --detect`.
- Livewire not finished: use `->waitForLivewire()` from `tests/Pest.php`.

## Dashboard numbers look wrong

- Only **expenses** count as actual cost (not POs, time entries or maintenance).
- Only **approved** change orders add to revenue.
- Only the **current** budget's forecast is used.
- `previous_billing_amount` is informational — it is not deducted from `net_amount`.

See [Dashboard](/docs/user-guide/dashboard#how-profitability-is-calculated).

## Where to look next

```bash
php artisan pail                    # live logs
tail -f storage/logs/laravel.log
php artisan about                   # environment summary
php artisan queue:failed
```
