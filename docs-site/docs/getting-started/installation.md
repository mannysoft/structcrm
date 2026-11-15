---
title: Installation
sidebar_position: 2
description: Get StructCRM running from a clean checkout in a few minutes.
---

# Installation

## Quick start

```bash
git clone https://github.com/mannysoft/structcrm.git
cd structcrm

# Backend
composer install
cp .env.example .env
php artisan key:generate

# Database (SQLite by default)
touch database/database.sqlite
php artisan migrate --seed

# Frontend
npm install
npm run build

# Serve
php artisan serve
```

Open [http://localhost:8000](http://localhost:8000). You will land on the marketing page; use **Log in** to sign
in with a seeded account (see [demo accounts](/docs/getting-started/seeding-and-demo-data#demo-staff-accounts)).

:::tip Shortcut
`composer run setup` performs install, `.env` creation, key generation, migration, asset
install and build in one command. It does **not** seed roles or demo users, so run
`php artisan db:seed` afterwards if you want them.
:::

## What each step does

1. **`composer install`** — installs Laravel 13, Livewire, Sanctum, spatie/laravel-permission,
   maatwebsite/excel, laravel/ai and the dev tooling (Pest, Pint, Dusk, Boost).
2. **`cp .env.example .env`** — creates your environment file. It defaults to SQLite,
   `database` sessions/cache/queue and the `log` mail driver.
3. **`php artisan key:generate`** — writes `APP_KEY`, required for encryption and sessions.
4. **`php artisan migrate --seed`** — creates the schema and runs `DatabaseSeeder`, which
   installs the seven staff roles plus demo users.
5. **`npm install && npm run build`** — compiles Tailwind 4 and the Vite entrypoints into
   `public/build`. Without this the app throws
   `Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest`.
6. **`php artisan serve`** — starts the development server.

## Verify the installation

```bash
php artisan about                       # environment summary
php artisan route:list --except-vendor  # confirm routes registered
curl http://localhost:8000/up           # health check, returns 200
```

## Database choice

SQLite is fine for development, demos and evaluation. For MySQL:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=structcrm
DB_USERNAME=root
DB_PASSWORD=
```

Then run `php artisan migrate --seed` again.

## Everyday development

```bash
composer run dev
```

This runs the PHP server, the queue listener, `pail` (live log tail) and Vite together,
restarting all of them if one exits. See [Running the app](/docs/getting-started/running-the-app).

## Storage

Uploads (site photos, documents, receipts, company logos) are written to the `public`
disk. Create the symlink once so files are reachable over HTTP:

```bash
php artisan storage:link
```

## Next step

[Configure the environment →](/docs/getting-started/configuration)
