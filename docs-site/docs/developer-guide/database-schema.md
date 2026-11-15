---
title: Database schema
sidebar_position: 16
description: The tables, how they relate, and the storage conventions behind them.
---

# Database schema

49 migrations define the schema. Every domain table carries `company_id`, a `uuid`, and
(where users can delete records) `deleted_at`.

## Conventions

| Convention | Detail |
| :--- | :--- |
| Primary key | `bigint unsigned` auto-increment `id` |
| Public identifier | `uuid` `char(36)`, unique, used in routes and API responses |
| Tenancy | `company_id` foreign key, indexed, cascade delete from `companies` |
| Money | `bigint` **minor units** (cents) — e.g. `unit_price`, `total`, `amount` |
| Currency | `char(3)` alongside the amount column |
| Status | `varchar(50)` cast to a backed enum on the model |
| Timestamps | `created_at`, `updated_at`; `deleted_at` for soft deletes |
| Quantities | `decimal(12,2)`; invoice/estimate/PO line quantities are `int` |
| Rates | `decimal(5,4)` for retention (e.g. `0.1000`) |

## Table groups

### Platform

| Table | Purpose |
| :--- | :--- |
| `users` | Staff and client-portal users (`type` = `staff` \| `client`, `client_id` for portal users) |
| `roles`, `permissions`, `model_has_roles`, `role_has_permissions`, `model_has_permissions` | spatie/laravel-permission |
| `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` | Database drivers |
| `personal_access_tokens` | Sanctum API tokens |
| `password_reset_tokens` | Password resets |
| `agent_conversations`, `agent_conversation_messages` | AI assistant history and approval state |

### Company and CRM

| Table | Notes |
| :--- | :--- |
| `companies` | `name`, `slug`, `logo_path`, `email`, `phone`, `address`, `tax_number`, `timezone`, `currency`, `getting_started_dismissed_at` |
| `clients` | `name`, `company_name`, contact fields, `notes` |
| `leads` | `first_name`, `last_name`, `email`, `phone`, `status`, `notes` |
| `suppliers` | Contact fields, `notes` |

### Projects and field

| Table | Notes |
| :--- | :--- |
| `projects` | `client_id`, `project_number`, `title`, `description`, `status`, `budget_amount`, `budget_currency`, `manager_id`, dates |
| `tasks` | `project_id`, `title`, `status`, `priority`, `assigned_user_id`, `due_date` |
| `daily_logs` | `project_id`, `user_id`, `log_date`, weather, crew, hours, narrative, issues, safety |
| `project_photos` | `project_id`, `user_id`, `category`, `file_path`, `caption` |
| `documents` | `project_id`, optional `change_order_id` / `billing_id`, `category`, `file_path` |
| `time_entries` | `project_id`, `user_id`, clock in/out, `break_minutes`, lat/long |
| `equipment` | `type`, `status`, `service_due_at`, `notes` (company-wide) |
| `maintenance_logs` | `equipment_id`, `service_date`, `description`, `cost_amount`, `cost_currency` |

### Finance

| Table | Notes |
| :--- | :--- |
| `contracts` | `contract_number`, `title`, `contract_value_amount`, `contract_value_currency`, `signed_at`, `status` |
| `cost_codes` | `code` (unique per company), `name`, `type`, `parent_id` |
| `boq_items` | `project_id`, `cost_code_id`, `item_code`, `description`, `quantity`, `unit`, `unit_price`, `total` |
| `budgets` | `project_id`, `type`, `version`, `is_current`, `status`, `forecast_amount` |
| `budget_items` | `budget_id`, optional `cost_code_id` / `boq_item_id`, quantity, unit, price, total |
| `progress_reports` | `report_number`, `report_date`, `status`, `notes` |
| `progress_items` | `progress_report_id`, `boq_item_id`, `cost_code_id`, `quantity`, `percent_complete`, `accomplished_amount` |
| `billings` | `billing_number`, period dates, `gross_amount`, `retention_rate`, `retention_amount`, `other_deductions_amount`, `net_amount`, `previous_billing_amount`, `status`, `certified_at`, `due_date` |
| `billing_items` | `billing_id`, optional `boq_item_id`, quantity, price, total |
| `billing_payments` | `billing_id`, `payment_date`, `amount`, `method`, `reference`, `notes` |

### Sales and commercial

| Table | Notes |
| :--- | :--- |
| `estimates` | `estimate_number`, `title`, `subtotal`, `tax_amount`, `amount`, `currency`, `status`, `valid_until`, optional `lead_id` / `project_id` |
| `estimate_items` | description, integer quantity, unit price, total |
| `invoices` | `invoice_number`, `status`, `total`, `issue_date`, `due_date` |
| `invoice_items` | description, integer quantity, unit price, total |
| `change_orders` | `co_number`, `description`, `amount` (**may be negative**), `amount_currency`, `status`, `approved_at` |
| `change_order_items` | `category`, description, `amount` |
| `purchase_orders` | `po_number`, `supplier_id`, `status`, `total` |
| `po_items` | description, integer quantity, unit price, total |
| `expenses` | `category`, `amount`, `currency`, `date`, `receipt_path`, `notes` |

### Marketing

| Table | Notes |
| :--- | :--- |
| `marketing_campaigns` | `name`, `description`, `is_active` (**platform-wide, no `company_id`**) |
| `marketing_campaign_steps` | `campaign_id`, `subject`, `delay_days`, `body`, `sort_order` |
| `marketing_email_logs` | `user_id`, `campaign_id`, `campaign_step_id`, `subject`, `sent_at` |

## Relationships

```text
companies
 ├── users (staff, company_id)        users (client portal, company_id + client_id)
 ├── clients ─┬── projects ─┬── contracts (1 used for revenue)
 │            │             ├── cost_codes (company-scoped, referenced by BOQ/budget/progress)
 │            │             ├── boq_items ──── progress_items / billing_items / budget_items
 │            │             ├── budgets (versioned, one is_current)
 │            │             ├── progress_reports ─── progress_items
 │            │             ├── billings ─── billing_items, billing_payments
 │            │             ├── estimates / invoices / expenses
 │            │             ├── change_orders ─── change_order_items
 │            │             ├── purchase_orders ─── po_items ─── suppliers
 │            │             ├── tasks / daily_logs / project_photos / documents
 │            │             └── time_entries
 │            └── (client portal users)
 ├── suppliers
 └── equipment ─── maintenance_logs
```

## Things that surprise people

- `documents` can attach to a change order or a billing (`change_order_id`, `billing_id`)
  even though the UI only creates project documents today.
- `marketing_*` tables have **no** `company_id`: campaigns are platform-wide.
- `equipment` and `cost_codes` are company-scoped, not project-scoped.
- `change_orders.amount` is a signed integer; everything else expects non-negative money.
- `billings.previous_billing_amount` is recorded for reference but not subtracted from
  `net_amount`.

## Inspecting the schema

```bash
php artisan db:table billings            # columns, indexes, foreign keys
php artisan db:show                      # database summary
php artisan db:monitor                   # connection stats
```
