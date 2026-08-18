# ConstructFlow — Build Roadmap & Remaining Work

Last updated: 2026-08-18

## Status at a glance

| Phase | Modules | Status |
| :--- | :--- | :--- |
| 1 — Foundation | Multi-tenant auth (Breeze + registration provisions a company), Company profile | ✅ done |
| 2 — CRM | Clients (repository + service), Leads | ✅ done |
| 3 — Projects | Projects, Tasks, Daily Logs, Site Photos, Documents | ✅ done |
| 4 — Financials | Estimates & items, Change Orders, Invoices & items, Expenses, Purchase Orders & Suppliers | ✅ done |
| 5 — Field tools & portal | Time Tracking, Fleet/Equipment + Maintenance Logs, Client Portal (tenant view) | ✅ done (see follow-up below) |
| 6 — Ecosystem | Public REST API, AI features | ⬜ not started |

Full master spec: `docs/prompts/complete-software-specification.md`
PRD: `docs/PRD.md`

## Remaining work

### 1. Client Portal — external client access (Phase 5 follow-up)
The current portal (`/portal`, `resources/views/livewire/portal/index.blade.php`) is a read-only
tenant view for authenticated company users. True external clients cannot log in yet.

To finish it:
- Add a `client_users` table (`client_id`, `email`, `password`, `invited_at`, `email_verified_at`, `company_id`).
- Add an invitation flow (company admin invites a client email → queued mail with magic link).
- Add a `client` auth guard (or reuse `users` with a `role` of `client` scoped to a `client_id`).
- Gate the portal on the client guard and scope all queries to the authenticated client's
  `client_id` rather than the user's `company_id`.
- Change-order approval + invoice payment actions inside the portal (currently read-only).

### 2. Public REST API hardening (Phase 6)
API routes currently exist for all modules but are **not behind auth** (see comment in
`routes/api.php`).
- Install Sanctum (`composer require laravel/sanctum`) and add token issuance endpoints.
- Wrap the API group in `auth:sanctum` middleware.
- Add rate limiting (throttle middleware) on API routes.
- Add API versioning (`/api/v1/...`) if desired.
- Consider API resource pagination (`paginate()` instead of `get()`) and OpenAPI docs.

### 3. AI features (Phase 6 roadmap)
- **Daily log summarization**: AI-generated weekly digest per project from `daily_logs`.
- **Automated material estimation**: predictive material requirements from project parameters.
- **Document Q&A**: natural-language Q&A over uploaded blueprints/contracts (`documents` table).

## Conventions a future session MUST follow
(These are verified, load-bearing patterns already recorded under `.ai/rules`.)

- Tests are **PHPUnit classes**, not Pest (`pestphp/pest` is not installed). Use
  `php artisan make:test --phpunit`.
- Domain models with an integer `id` PK **and** a `uuid` column must override
  `public function uniqueIds(): array { return ['uuid']; }` (plain `HasUuids` would UUID the `id`).
- Class-based full-page Volt components use
  `new #[Layout('layouts.app')] class extends Component {}` (the `layout()` helper only works
  with functional-style components).
- Clients use **repository + service**; all other domains use **Action classes**
  (`CreateXAction`, `UpdateXAction`, `DeleteXAction`, wrapped in `DB::transaction`).
- Money is integer cents + `char(3)` currency, hydrated via a `Money` property hook
  (see `app/Domains/Sales/Models/Expense.php` and `app/Common/ValueObjects/Money.php`).
- Line-item child models need an explicit `protected $table` (e.g. `po_items`, `invoice_items`).
- Every module ships: migration, model, DTO, actions, policy, Store/Update requests, resource,
  controller, factory, seeder, Volt `index`/`create`/`edit` views, web + API routes
  (API routes live under `Route::name('api.')->group(...)` to avoid web route-name collisions),
  nav links, and PHPUnit controller + action tests **including a cross-tenant isolation test**.
- Observers generate sequential numbers via `#[ObservedBy]` (see `ProjectObserver`, `InvoiceObserver`).
- Enums use **TitleCase keys** (`case Draft = 'draft';`).

## Verification commands

```bash
vendor/bin/pint --dirty --format agent   # format changed PHP files
php artisan test --compact               # full test suite (currently 154 tests, 377 assertions)
php artisan migrate --no-interaction     # apply new migrations to the dev DB
```

## Current test baseline
- 154 tests, 377 assertions — all passing as of 2026-08-18.
