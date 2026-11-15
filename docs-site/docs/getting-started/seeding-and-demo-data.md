---
title: Seeding and demo data
sidebar_position: 4
description: The roles, permissions and demo accounts that seeding creates, and how to load sample project data.
---

# Seeding and demo data

## Roles and permissions

`RolePermissionSeeder` creates every permission and assigns it to the eight staff roles.

Permissions are coarse-grained and mirrored by the model policies:

```text
manage_company, manage_users, manage_projects, manage_contracts, manage_cost_codes,
manage_boq, manage_budgets, manage_progress_reports, manage_tasks, manage_estimates,
manage_change_orders, manage_invoices, manage_billings, manage_payments, manage_expenses,
manage_purchase_orders, manage_suppliers, manage_clients, manage_leads, manage_documents,
manage_photos, manage_daily_logs, manage_time_entries, manage_equipment,
manage_maintenance_logs, manage_marketing
```

| Role | Permissions |
| :--- | :--- |
| `owner` | Everything |
| `administrator` | Everything |
| `project_manager` | Projects, contracts, BOQ, budgets, progress reports, billings, tasks, estimates, change orders, invoices, clients, leads, documents, photos, daily logs, time entries |
| `quantity_surveyor` | Projects, contracts, cost codes, BOQ, budgets, progress reports, billings, change orders, clients, documents |
| `engineer` | Documents, photos, daily logs |
| `foreman` | Photos, daily logs, time entries |
| `staff` | Time entries |
| `accountant` | Invoices, billings, payments, expenses, purchase orders, suppliers |

The client portal user is not a staff role: portal users are `users` rows with
`type = client`, linked to a `client_id`, and use the separate `client` auth guard.

## Demo staff accounts

`UserSeeder` creates a demo company (**ConstructFlow Demo Builders**) and one user per
role. The password for every account is `password`.

| Role | Email |
| :--- | :--- |
| owner | `owner@example.com` |
| administrator | `admin@example.com` |
| project_manager | `manager@example.com` |
| engineer | `engineer@example.com` |
| foreman | `foreman@example.com` |
| staff | `staff@example.com` |
| accountant | `accountant@example.com` |

`DatabaseSeeder` runs `RolePermissionSeeder` and `UserSeeder`, then creates one extra user:
`test@example.com` (also password `password`).

```bash
php artisan db:seed
```

:::danger[Demo credentials]
These accounts are for local evaluation only. Never seed them into a public deployment.
:::

## Sample module data

Every module ships its own seeder under `database/seeders` (`ClientSeeder`,
`ProjectSeeder`, `BoqItemSeeder`, `BillingSeeder`, `InvoiceSeeder`, …). They are **not**
called by `DatabaseSeeder`, and most of them create their **own** demo company, so run
them on a throwaway database:

```bash
php artisan migrate:fresh
php artisan db:seed --class=ProjectSeeder
```

To load a broad demo dataset in dependency order:

```bash
for seeder in ClientSeeder SupplierSeeder CostCodeSeeder ProjectSeeder ContractSeeder \
  BoqItemSeeder BudgetSeeder TaskSeeder DailyLogSeeder ProgressReportSeeder \
  BillingSeeder EstimateSeeder InvoiceSeeder ExpenseSeeder ChangeOrderSeeder \
  PurchaseOrderSeeder TimeEntrySeeder EquipmentSeeder; do
  php artisan db:seed --class="$seeder"
done
```

Check `database/seeders/*.php` before relying on a seeder: each one documents the company
and records it creates.

## Self-service sign-up

Registration (`/register`) is open by default. Submitting the form creates a new company,
an `owner` user scoped to it, and records marketing consent if the box is ticked. This is
the fastest way to get a clean tenant while evaluating the app.

## Next step

[Run the app →](/docs/getting-started/running-the-app)
