# ConstructFlow

Enterprise-grade, multi-tenant **Construction Project Management & CRM SaaS** built with Laravel. It replaces spreadsheets, messaging threads, and paper logs with a single real-time platform for general contractors, subcontractors, and field crews.

## Features

- **Multi-tenant isolation** — single database with `company_id` scoping enforced by a global `TenantScope` on every domain model.
- **Team & role management** — staff invitations and role-based permissions (`spatie/laravel-permission`).
- **CRM** — clients and a lead pipeline.
- **Projects & field tools** — projects, tasks, daily logs, site photos, documents, time tracking, equipment and maintenance logs.
- **Financials** — estimates (+ line items), change orders, invoices (+ line items), expenses, purchase orders and suppliers.
- **Client portal** — external clients log in to view progress, approve change orders, and mark invoices paid.
- **REST API** — token-authenticated JSON API for every module (`laravel/sanctum`).

## Tech stack

- PHP 8.4+ / Laravel 13
- Livewire 3 + Volt (class-based full-page components)
- Tailwind CSS 4 + Vite
- SQLite (dev/test) or MySQL (production)
- `spatie/laravel-permission`, `laravel/sanctum`, `laravel/breeze`
- Pest for testing

## Requirements

- PHP 8.4+ with the `mbstring`, `dom`, `fileinfo`, and `sqlite3`/`pdo_sqlite` extensions
- Composer
- Node.js 20+ and npm

## Getting started

```bash
git clone <repo-url> && cd structcrm

# Backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed     # seeds roles, permissions, and a demo user

# Frontend
npm install
npm run build                 # use `npm run dev` during development

# Serve
php artisan serve
```

Or run the whole development stack (server, queue, logs, Vite) together:

```bash
composer run dev
```

### Seeding

`php artisan db:seed` runs `RolePermissionSeeder` (the 7 staff roles and `manage_*` permissions) plus a demo user. Every domain module also ships its own seeder under `database/seeders`.

## Testing

Tests use [Pest](https://pestphp.com) and run against an in-memory SQLite database.

```bash
php artisan test                     # run the whole suite
php artisan test --filter=TestName   # run a single test
vendor/bin/pint --dirty              # format code
```

### Browser tests (Dusk)

Browser tests use [Laravel Dusk](https://laravel.com/docs/dusk) with Pest and drive a real Chrome browser against a running app server.

Dusk does not start the app server itself, so prepare a dedicated SQLite database and start the server before running the tests:

```bash
# 1. Prepare the Dusk database
touch database/dusk.sqlite
DB_CONNECTION=sqlite DB_DATABASE=database/dusk.sqlite php artisan migrate:fresh --force
DB_CONNECTION=sqlite DB_DATABASE=database/dusk.sqlite php artisan db:seed --class=RolePermissionSeeder --force

# 2. Start the server (must point at the same database)
APP_URL=http://127.0.0.1:8000 DB_CONNECTION=sqlite DB_DATABASE=database/dusk.sqlite \
  php artisan serve --host=127.0.0.1 --port=8000 --no-reload

# 3. Run the tests (in another terminal)
php artisan dusk                      # run all browser tests
php artisan dusk --filter=AuthenticationTest
```

Dusk runs against `.env.dusk.local` (SQLite at `database/dusk.sqlite`, base URL `http://127.0.0.1:8000`). Requires Chrome; `php artisan dusk:chrome-driver --detect` installs the matching driver if Chrome updates. The `--no-reload` flag keeps the server from restarting when the `dusk` command temporarily swaps the `.env` file.

## Project structure

- `app/Domains/{CompanyManagement,ProjectManagement,Sales}` — domain modules (models, DTOs, actions, policies, requests, resources, controllers).
- `app/Common` — shared traits, scopes, and value objects (e.g. `BelongsToTenant`, `TenantScope`, `Money`).
- `app/Livewire`, `resources/views/livewire` — Volt components and pages.
- `routes/web.php`, `routes/api.php` — web routes and the token-authenticated API.

## Documentation

- [Product Requirements (PRD)](docs/PRD.md)
- [Architecture](docs/ARCHITECTURE.md)
- [Database schema](docs/DATABASE.md)
- [Roadmap](docs/ROADMAP.md)

## License

Licensed under the [MIT license](https://opensource.org/licenses/MIT).
