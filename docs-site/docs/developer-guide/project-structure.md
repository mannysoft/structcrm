---
title: Project structure
sidebar_position: 2
description: Where every kind of file lives in this repository.
---

# Project structure

```text
app/
├── Ai/
│   ├── Agents/CrmAssistant.php        # The single assistant agent
│   └── Tools/                         # Analytics, Company, Finance, Marketing, Projects, Sales
├── Common/
│   ├── Scopes/TenantScope.php         # Global company_id scope
│   ├── Traits/BelongsToTenant.php     # Adds the scope + auto-fills company_id
│   └── ValueObjects/Money.php         # Integer-cent money
├── Console/Commands/                  # marketing:send-due-emails
├── Domains/
│   ├── CompanyManagement/             # Company, users, roles, getting-started checklist
│   ├── ProjectManagement/             # Projects, tasks, logs, photos, documents, time, equipment
│   ├── Finance/                       # Contracts, cost codes, BOQ, budgets, reports, billings
│   ├── Sales/                         # Clients, leads, suppliers, estimates, invoices, COs, POs, expenses
│   └── Marketing/                     # Campaigns, steps, email logs, Resend sync
├── Http/
│   ├── Controllers/                   # Base controller, auth, AI, BOQ template, unsubscribe
│   └── Middleware/EnsureGettingStartedPrerequisites.php
├── Livewire/{Actions,Forms}           # Logout action, login form
├── Models/User.php
├── Providers/AppServiceProvider.php   # Rate limiters
└── View/                              # Blade components/helpers

database/
├── factories/                         # One per model
├── migrations/                        # 49 migrations
└── seeders/                           # RolePermissionSeeder, UserSeeder, one per module

resources/
├── css/app.css                        # Tailwind 4 theme (brand palette)
├── js/app.js
└── views/
    ├── components/                    # x-table, x-badge, x-button, x-modal, …
    ├── layouts/{app,guest}.blade.php
    └── livewire/                      # Full-page components, one folder per module

routes/
├── web.php    # Staff app, portal, signed invite/unsubscribe routes
├── api.php    # Sanctum-protected JSON API
├── auth.php   # Login, register, password reset, email verification
└── console.php

tests/
├── Feature/   # Pest feature tests (in-memory SQLite)
├── Unit/
├── Browser/   # Laravel Dusk browser tests
└── Pest.php   # Global uses(), helpers and Dusk macros
```

## Naming conventions

| Thing | Convention | Example |
| :--- | :--- | :--- |
| Domain folder | PascalCase, singular noun phrase | `ProjectManagement` |
| Action | `Verb` + `Noun` + `Action` | `CreateBillingAction`, `DecideChangeOrderAction` |
| DTO | `Noun` + `Data` | `BillingData`, `LeadData` |
| Form request | `Store`/`Update` + `Noun` + `Request` | `StoreLeadRequest` |
| Policy | `Noun` + `Policy` | `LeadPolicy` |
| API resource | `Noun` + `Resource` | `LeadResource` |
| Observer | `Noun` + `Observer` | `BillingObserver` |
| Livewire page | `views/livewire/<module>/<page>.blade.php` | `billings/create.blade.php` |
| Test | `Noun` + `Test.php` | `ChangeOrderPageTest.php` |

## Editor and AI tooling

`.ai/rules` holds committed, path-scoped conventions used by Laravel Boost. Read
`.ai/rules/index.md` before changing a file in a covered path — several rules there are
non-obvious traps (money hook naming, snake_case route params, `uniqueIds()` overrides).
