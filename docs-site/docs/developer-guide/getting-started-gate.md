---
title: Getting-started gate
sidebar_position: 8
description: How the onboarding checklist blocks create routes until prerequisites exist.
---

# Getting-started gate

New tenants cannot create records out of order: a project needs a client, and almost
everything else needs a project. The gate lives in three files.

| File | Responsibility |
| :--- | :--- |
| `app/Domains/CompanyManagement/Services/GettingStartedService.php` | Defines steps, computes progress, checks completion |
| `app/Http/Middleware/EnsureGettingStartedPrerequisites.php` | Maps route names to the step they require |
| `resources/views/livewire/getting_started/index.blade.php` | The checklist UI |

The middleware alias is registered in `bootstrap/app.php`:

```php
$middleware->alias([
    'getting-started' => EnsureGettingStartedPrerequisites::class,
]);
```

## Steps

`GettingStartedService::STEP_KEYS` defines the order:

```text
company_profile → cost_codes → clients → suppliers
               → projects → contracts → boq_items → invoices
```

Each step names a route, an action label, and (where relevant) the model whose existence
counts as completion. `company_profile` is complete when email, phone, address, tax number
and timezone are all filled.

## Route gate

`EnsureGettingStartedPrerequisites::PREREQUISITES` maps create routes to a required step:

| Route | Requires |
| :--- | :--- |
| `projects.create` | `clients` |
| `contracts.create`, `boq_items.create`, `boq_items.import`, `budgets.create`, `billings.create`, `invoices.create`, `change_orders.create`, `expenses.create`, `tasks.create`, `daily_logs.create`, `progress_reports.create`, `project_photos.create`, `documents.create`, `time_entries.create`, `purchase_orders.create` | `projects` |

When the requirement is missing the user is redirected to the checklist with
`getting_started_notice` explaining which step is outstanding.

Routes are opted in via `routes/web.php`:

```php
Route::livewire('projects/create', 'projects.create')
    ->middleware('getting-started')
    ->name('projects.create');
```

## Dismissal

`companies.getting_started_dismissed_at` hides the checklist from the dashboard and from
the Administration nav group. It **does not bypass the gate** — dismissal is cosmetic, so a
user can hide the noise without unlocking anything.

## Adding a step

1. Add the key to `GettingStartedService::STEP_KEYS` in the right position.
2. Add an entry to `GettingStartedService::STEPS` with `title`, `description`, `route`,
   `action`, `model` and `requires`.
3. Add `->middleware('getting-started')` to the create route.
4. Map the route name in `EnsureGettingStartedPrerequisites::PREREQUISITES`.
5. Add a feature test asserting the redirect and the unlock (see
   `tests/Feature/GettingStartedTest.php`).

:::note Rule of record
This workflow is recorded in `.ai/rules/routes.md`. Read it before adding a create route.
:::
