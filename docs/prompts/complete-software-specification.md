# ConstructFlow — Complete Software Specification

> **Purpose:** A self-contained, copy-pasteable master build prompt. Feed this file to a coding
> agent to build the application incrementally, module by module. It consolidates the PRD,
> architecture, database design, and verified project conventions into a single specification.

---

## Role

You are a Senior Laravel Architect with 15+ years of experience building enterprise, multi-tenant
SaaS applications. You design and build production-ready software. Never generate prototype or
throwaway code. Always follow SOLID, Clean Architecture, Domain-Driven Design (where appropriate),
Laravel best practices, and modern PHP standards.

Before writing code for any module, explain the architectural decision you are making.

---

## 1. Product Overview

**ConstructFlow** is an enterprise-grade, multi-tenant Construction Project Management & CRM SaaS.
It replaces spreadsheets, messaging threads, and paper logs with one real-time cloud platform for
general contractors, home builders, and specialty subcontractors (electrical, plumbing, HVAC,
roofing, masonry, painting, landscape/civil, fit-out).

**Core objectives**

- Win more jobs: lead tracking, estimates, client quotations.
- Master field + office operations: daily logs, change orders, photo documentation.
- Track costs & margins: budgets, expenses, purchase orders, time tracking, invoicing.
- Strict tenant isolation: zero cross-company data leakage.

---

## 2. Tech Stack

| Layer | Technology | Version |
| :--- | :--- | :--- |
| Language | PHP | 8.4+ |
| Framework | Laravel | 13.x |
| Reactive UI | Livewire + Volt | 3.x (functional + class-based Volt) |
| Client scripting | Alpine.js | 3.x |
| Styling | Tailwind CSS | 4.x (via Vite) |
| Database | MySQL (InnoDB, utf8mb4) | 8.0+ |
| Cache & Queue | Redis + Laravel Horizon | 7.0+ |
| Storage | Amazon S3 / S3-compatible | — |
| Auth | Laravel Breeze (Livewire/Volt stack) | 2.x |
| Payments | Stripe (Laravel Cashier) | — |
| Notifications | Mail, Database, Broadcast | — |
| Testing | PHPUnit | 12.x |

---

## 3. Multi-Tenancy Strategy

Single-database, shared-schema isolation.

- Every tenant-owned table has a `company_id` foreign key referencing `companies.id`
  (`ON DELETE CASCADE`).
- A `BelongsToTenant` trait adds a global scope that filters by `auth()->user()->company_id`
  and auto-populates `company_id` on create.
- Domain models expose `uuid` (public identifier, route key) alongside an auto-increment
  integer `id` (primary key, FK target).
- Authorization is enforced by Policies comparing `user->company_id` to the resource's
  `company_id`.

---

## 4. Roles & Permissions

| Role | Capabilities |
| :--- | :--- |
| Owner | Full admin, financial, billing, and system access. |
| Administrator | Company profile, user invitation, role management, system settings. |
| Project Manager | Create/manage projects, budgets, estimates, change orders, invoices, tasks. |
| Engineer | Review designs, approve inspections, monitor timelines and daily logs. |
| Foreman | Submit daily logs, weather/delays, site photos, crew hours. |
| Staff / Trades | View assigned tasks, clock in/out, view project files. |
| Accountant | Invoices, expenses, purchase orders, financial reporting, payments. |
| Client | External portal: view progress, approve change orders, download files, pay invoices. |

Implement role authorization with Policies and Gates. Where granular role checks are deferred,
leave an explicit `// TODO: role check` comment rather than silently allowing.

---

## 5. Module Specifications

### 5.1 Company & Multi-Tenant Management

- Company profile fields: `name`, `logo`, `email`, `phone`, `address`, `tax_number`, `timezone`,
  `currency`, plus generated `slug` and `uuid`.
- Registration provisions a **Company + Owner User** atomically (create both in one transaction).
- Tenant lifecycle: soft delete.

### 5.2 Clients (CRM)

- Belongs to one company.
- Fields: `name`, `company_name`, `email`, `phone`, `address`, `notes`.
- Use the **Repository + Service pattern** for Clients:
  - `Repositories/ClientRepository` — scoped persistence.
  - `Services/ClientService` — business logic consuming the repository and a `ClientData` DTO.
- Full CRUD (API + Livewire index/create/edit).

### 5.3 Leads (CRM Pipeline)

- Belongs to one company.
- Fields: `first_name`, `last_name`, `email`, `phone`, `status`, `notes`.
- Pipeline statuses: `New`, `Contacted`, `Qualified`, `Lost`, `Converted`.

### 5.4 Projects

- Belongs to one **Company** and one **Client**.
- Fields: `project_number` (auto-generated, e.g. `PRJ-2026-001`), `title`, `description`,
  `status`, `budget` (money value object), `start_date`, `end_date`, `manager` (user FK).
- Statuses: `Draft`, `Pending`, `Active`, `On Hold`, `Completed`, `Archived`.
- Use the **Action-class pattern** (`CreateProjectAction`, `UpdateProjectAction`,
  `DeleteProjectAction`) plus an Observer that assigns the next sequential `project_number`.

### 5.5 Estimates & Change Orders

- **Estimates:** belong to a company, optionally a lead and/or project. Fields: `estimate_number`,
  `title`, line items (materials, labor, equipment, markup, tax, discount), `status`
  (`Draft`, `Sent`, `Approved`, `Rejected`, `Converted`), `valid_until`, `subtotal`, `tax_amount`,
  `total_amount`.
- **Change Orders:** belong to a project. Fields: `co_number` (e.g. `CO-104`), `description`,
  `amount` (+/-), `status` (`Pending`, `Approved`, `Rejected`), `approved_at`.
- 1-click estimate → project conversion.

### 5.6 Tasks & Schedule

- Belong to a project.
- Fields: `title`, `description`, `status` (`To Do`, `In Progress`, `In Review`, `Done`),
  `priority` (`Low`, `Medium`, `High`, `Urgent`), `assigned_user_id`, `due_date`, `milestone_id`.
- Milestones link tasks to phases; track dependencies and critical path.

### 5.7 Daily Logs (Field Reporting)

- Belong to a project; authored by a user.
- Fields: `log_date`, `weather`, `crew_count`, `labor_hours`, `equipment_usage`, `work_performed`,
  `issues`, `safety_incidents`.

### 5.8 Site Photos

- Belong to a project; uploaded by a user.
- Fields: `category` (`Before`, `During`, `After`, `Inspection`, `Safety`), `file_path`, `caption`.
- Secure upload with image validation, size limits, and S3 storage.

### 5.9 Document Repository

- Belong to a project. Fields: `name`, `file_path`, `category` (blueprint, CAD, permit, contract,
  spec). S3-backed with signed URLs.

### 5.10 Invoicing

- Belong to a project. Fields: `invoice_number`, line items, `status`
  (`Draft`, `Sent`, `Partial`, `Paid`, `Overdue`), `amount`, `issue_date`, `due_date`.
- PDF generation queued.

### 5.11 Expenses

- Belong to a project. Fields: `category` (labor, fuel, materials, equipment, subcontract),
  `amount`, `date`, `receipt_path`, `notes`.

### 5.12 Purchase Orders & Suppliers

- **Suppliers:** `name`, `email`, `phone`, `address`, `notes`.
- **Purchase Orders:** belong to a project and supplier. Fields: `po_number`, line items, `status`
  (`Draft`, `Sent`, `Approved`, `Received`), `total`.

### 5.13 Time Tracking

- Belong to a user/project. Clock-in/clock-out with optional GPS geofence and work/break breakdown.

### 5.14 Equipment & Fleet

- Fleet items: `name`, `type`, `status`, `service_due_at`, `notes`. Maintenance log entries.

### 5.15 Client Portal

- Restricted, read-mostly view for external Clients: progress, photos, contracts, change order
  approval, invoice payment.

### 5.16 AI Features (Phase 4 roadmap)

- Daily log summarization, automated material estimation, document Q&A over blueprints/contracts.

---

## 6. Architecture

### 6.1 Layered Pattern

```
Request → Middleware (auth + tenant) → Livewire/Volt or Controller
        → FormRequest (validation + authorization)
        → Action/Service (single responsibility, wrapped in a transaction)
        → Eloquent Model (global tenant scope) → Event → Queued Job/Listener
```

- **Controllers / Volt components** coordinate only; no business logic in them or in Blade.
- **Action classes** (single-purpose transactions) or **Service classes** hold business logic.
- **DTOs** (readonly) carry data between layers.
- **Value Objects** (e.g. `Money`) model domain concepts.
- **Observers** handle side effects like slug/project-number generation.
- **Events** notify other parts of the system; **Jobs** handle async work (PDF, email, S3).

### 6.2 Directory Structure

```
app/
├── Common/
│   ├── Scopes/            # TenantScope
│   ├── Traits/            # BelongsToTenant
│   └── ValueObjects/      # Money, etc.
├── Domains/
│   ├── CompanyManagement/ # Company
│   ├── ProjectManagement/ # Project, Task, DailyLog, Photo, Document
│   └── Sales/             # Client, Lead, Estimate, Invoice, Expense, PO, Supplier
│       ├── Actions/  (or) Repositories/ + Services/
│       ├── Controllers/
│       ├── DTOs/
│       ├── Enums/
│       ├── Events/
│       ├── Models/
│       ├── Observers/
│       ├── Policies/
│       ├── Requests/
│       └── Resources/
├── Http/Controllers/
├── Jobs/  Events/  Notifications/
└── Providers/
```

### 6.3 Pattern selection

- **Repository + Service**: Clients (explicit requirement). Repository encapsulates Eloquent
  queries; Service orchestrates and consumes DTOs.
- **Action classes**: Projects, and other domains, unless told otherwise. One class per operation
  (`CreateXAction`, `UpdateXAction`, `DeleteXAction`).

---

## 7. Database Rules

For every new entity, generate: **Migration, Model, Factory, Seeder, Policy, FormRequest(s),
Resource, Controller, and tests.**

- All tenant tables include `company_id` FK and a `uuid` unique column.
- High-throughput tables add composite indexes: `(company_id, status)` and
  `(company_id, created_at)`.
- `ON DELETE CASCADE` on secondary relationships; soft deletes on `companies`, `projects`,
  and `clients`.
- Money is stored as integer cents + `char(3)` currency code (use a `Money` value object).

### Core tables

| Table | Key columns (beyond id, uuid, company_id, timestamps) |
| :--- | :--- |
| `companies` | `name`, `slug`, `logo_path`, `email`, `phone`, `address`, `tax_number`, `timezone`, `currency` |
| `users` | `company_id`, `name`, `email`, `password`, `role`, `phone`, `avatar_path` |
| `clients` | `name`, `company_name`, `email`, `phone`, `address`, `notes` |
| `leads` | `first_name`, `last_name`, `email`, `phone`, `status`, `notes` |
| `projects` | `client_id`, `project_number`, `title`, `description`, `status`, `budget_amount`, `budget_currency`, `manager_id`, `start_date`, `end_date` |
| `estimates` | `lead_id`, `project_id`, `estimate_number`, `title`, `status`, `subtotal`, `tax_amount`, `total_amount`, `valid_until` |
| `estimate_items` | `estimate_id`, `description`, `quantity`, `unit_price`, `total` |
| `change_orders` | `project_id`, `co_number`, `description`, `amount`, `status`, `approved_at` |
| `tasks` | `project_id`, `milestone_id`, `title`, `status`, `priority`, `assigned_user_id`, `due_date` |
| `daily_logs` | `project_id`, `user_id`, `log_date`, `weather`, `crew_count`, `labor_hours`, `work_performed`, `issues` |
| `project_photos` | `project_id`, `user_id`, `category`, `file_path`, `caption` |
| `documents` | `project_id`, `name`, `category`, `file_path` |
| `invoices` | `project_id`, `invoice_number`, `status`, `amount`, `issue_date`, `due_date` |
| `invoice_items` | `invoice_id`, `description`, `quantity`, `unit_price`, `total` |
| `expenses` | `project_id`, `category`, `amount`, `date`, `receipt_path`, `notes` |
| `suppliers` | `name`, `email`, `phone`, `address`, `notes` |
| `purchase_orders` | `project_id`, `supplier_id`, `po_number`, `status`, `total` |
| `po_items` | `purchase_order_id`, `description`, `quantity`, `unit_price`, `total` |

---

## 8. Code Quality & Conventions

- `declare(strict_types=1);` and full type declarations everywhere.
- Use PHP attributes: `#[ObservedBy]`, `#[UseFactory]`, `#[Layout]`, `#[Validate]`,
  `#[Fillable]`, `#[Hidden]`.
- Use enums with **TitleCase keys** (`case Draft = 'draft';`).
- Use readonly DTOs, readonly promoted constructor properties, and value objects.
- Use Laravel Collections; avoid duplicated logic; reuse existing components.
- **Verified repo conventions (must follow):**
  - Tests are **PHPUnit classes** (not Pest). `pestphp/pest` is not installed.
  - Domain models with an integer `id` PK **and** a `uuid` column must override
    `public function uniqueIds(): array { return ['uuid']; }` (plain `HasUuids` would UUID the `id`).
  - Class-based full-page Volt components use `new #[Layout('layouts.app')] class extends Component {}`.
  - Clients use repository+service; Projects/Company use Action classes.
- Run `vendor/bin/pint --dirty --format agent` after touching PHP.

---

## 9. Security

- Policies + authorization on every model action.
- Validation on every input path (FormRequests for API; `#[Validate]` for Volt).
- CSRF, rate limiting on auth/API endpoints, secure file uploads (image validation, size cap).
- Never trust client input; scope `exists` rules to the authenticated company.

---

## 10. UI Requirements

- Modern, minimal, responsive SaaS UI (Tailwind 4, Inter font).
- Reusable Livewire components; no business logic in Blade.
- Consistent app shell via `layouts.app` + `livewire.layout.navigation`.

---

## 11. Testing Requirements

- PHPUnit feature + unit tests using `RefreshDatabase` and model factories.
- Cover happy paths, failure paths, edge cases, and **cross-tenant isolation** (User in Company A
  cannot read/update/delete Company B data).
- Never remove existing tests without approval.

---

## 12. Build Order (run one phase at a time)

1. **Phase 1 — Foundation:** multi-tenant auth (Breeze + registration provisioning a company),
   Company profile.
2. **Phase 2 — CRM:** Clients (repository + service), Leads.
3. **Phase 3 — Projects:** Projects, Tasks, Daily Logs, Site Photos, Documents.
4. **Phase 4 — Financials:** Estimates & items, Change Orders, Invoices & items, Expenses,
   Purchase Orders & Suppliers.
5. **Phase 5 — Field tools & portal:** Time tracking, Fleet/Equipment, Client Portal.
6. **Phase 6 — Ecosystem:** Public REST API, AI summaries & document Q&A.

---

## 13. Output Format (for every module)

1. Architecture explanation.
2. Database design.
3. File structure.
4. Laravel code (production-ready).
5. Tests (PHPUnit).
6. Improvements / next steps.
