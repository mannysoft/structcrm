---
title: Running the app
sidebar_position: 5
description: Development workflows, the combined dev command, and what needs a queue worker.
---

# Running the app

## Everything at once

```bash
composer run dev
```

Runs four processes with `concurrently` and kills them together:

| Process | Command | Purpose |
| :--- | :--- | :--- |
| `server` | `php artisan serve` | Application at [http://localhost:8000](http://localhost:8000) |
| `queue` | `php artisan queue:listen --tries=1 --timeout=0` | Sends queued mail (invitations, marketing) |
| `logs` | `php artisan pail --timeout=0` | Live log output |
| `vite` | `npm run dev` | Hot module replacement for assets |

## Running pieces separately

```bash
php artisan serve                 # app only
npm run dev                       # assets with HMR
php artisan queue:work            # production-style worker
php artisan schedule:work         # run the scheduler locally
```

:::note Front-end changes not showing?
Re-run `npm run build` (or keep `npm run dev` running). A stale manifest is the most
common cause of missing styles.
:::

## Health checks

| Endpoint | Meaning |
| :--- | :--- |
| `GET /up` | Application booted successfully |
| `GET /` | Public marketing page |
| `GET /dashboard` | Authenticated dashboard (redirects to login) |

## Authentication surfaces

| Surface | Route | Guard |
| :--- | :--- | :--- |
| Staff login | `/login` | `web` (users with `type = staff`) |
| Staff registration | `/register` | `web`, creates a company group |
| Client portal login | `/portal/login` | `client` |
| Password set (invitations) | `/staff/set-password/{user}`, `/portal/set-password/{user}` | signed URLs |
| API tokens | `POST /api/login` | Sanctum |

Guests hitting a `/portal*` URL are redirected to the portal login rather than the staff
login.

## Queues in development

With `QUEUE_CONNECTION=database`, mail is queued. If no worker is running, invitations and
marketing emails sit in the `jobs` table. Either run `composer run dev` (which starts a
worker) or use `QUEUE_CONNECTION=sync` while debugging mail.

## Tests

```bash
php artisan test --compact                  # Pest suite (in-memory SQLite)
php artisan test --compact --filter=LeadTest
vendor/bin/pint --dirty                     # format changed files
php artisan dusk                            # browser tests, needs a running server
```

Dusk requires a dedicated database and a manually started server — see the repository
`README.md` for the exact sequence, or the [testing guide](/docs/developer-guide/testing).

## Next step

[Complete the first-run checklist →](/docs/getting-started/first-run-checklist)
