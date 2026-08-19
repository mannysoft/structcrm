# ConstructFlow — Build Roadmap & Remaining Work

Last updated: 2026-08-19

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

### 1. Client Portal — external client access (Phase 5 follow-up) ✅ done
External clients can now log in to the portal (`/portal`) and are scoped to their own records.

Implementation (single `users` table approach):
- Reuses the existing `users` table — a `type` column (`staff` | `client`) plus a nullable
  `client_id` discriminate client portal users (no separate `client_users` table).
- A dedicated `client` auth guard (session driver, same `users` provider) keeps staff and client
  sessions separate. Staff login is constrained to `type = staff` and portal login to `type = client`.
- Invitation flow: admin invites a client email from the Clients edit page →
  `InviteClientUserAction` creates/updates the client user and queues a `ClientInvitation`
  mail with a signed, 7-day "set password" link (`portal.set-password`).
- Portal routes are gated on `auth:client` and scope all queries to the authenticated user's
  `client_id`; unauthenticated portal hits redirect to `portal.login`.
- Change-order approve/reject and invoice "mark as paid" actions are available in the portal.

### 2. Public REST API hardening (Phase 6) ✅ done
- Installed Sanctum and added `HasApiTokens` to `User`.
- Token issuance (`POST /api/login`) and revocation (`POST /api/logout`) in `ApiTokenController`.
- All module API routes are wrapped in `auth:sanctum` + `throttle:api` (60/min limiter in
  `AppServiceProvider`); login is rate-limited at `throttle:6,1`.

Deferred (optional): `/api/v1` versioning prefix and resource pagination + OpenAPI docs.

### 3. AI features (Phase 6 roadmap)
- **Daily log summarization**: AI-generated weekly digest per project from `daily_logs`.
- **Automated material estimation**: predictive material requirements from project parameters.
- **Document Q&A**: natural-language Q&A over uploaded blueprints/contracts (`documents` table).

## Conventions a future session MUST follow
(These are verified, load-bearing patterns already recorded under `.ai/rules`.)

- Tests use **Pest** (`pestphp/pest`) in the functional style — `test('...', fn () => ...)` with
  `$this` bound to `Tests\TestCase` (see `tests/Pest.php`). Use `php artisan make:test`.
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
  nav links, and Pest controller + action tests **including a cross-tenant isolation test**.
- Observers generate sequential numbers via `#[ObservedBy]` (see `ProjectObserver`, `InvoiceObserver`).
- Enums use **TitleCase keys** (`case Draft = 'draft';`).
- Client portal users share the `users` table — discriminated by `type` (`staff` | `client`) and
  a nullable `client_id`, authenticated via a separate `client` guard (see `config/auth.php`).
- Staff roles/permissions use `spatie/laravel-permission`: roles + `manage_*` permissions are seeded
  by `RolePermissionSeeder`; policies gate with `$user->hasPermissionTo('manage_*')`. The founding
  registration user is assigned `owner`; `UserFactory` assigns `owner` by default (use
  `->role('staff')` to restrict in tests).

## Verification commands

```bash
vendor/bin/pint --dirty --format agent   # format changed PHP files
php artisan test --compact               # full test suite (currently 191 tests, 464 assertions)
php artisan migrate --no-interaction     # apply new migrations to the dev DB
```

## Current test baseline
- 191 tests, 464 assertions — all passing as of 2026-08-19.
