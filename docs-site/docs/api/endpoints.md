---
title: Endpoints
sidebar_position: 3
description: Every route, its payload and its notable fields.
---

# Endpoints

Twenty-four resources follow the standard REST pattern:

```text
GET    /api/{resource}          index
POST   /api/{resource}          store
GET    /api/{resource}/{id}     show
PUT    /api/{resource}/{id}     update
DELETE /api/{resource}/{id}     destroy
```

plus three special routes: `POST /api/billings/{billing}/payments`, `POST /api/assistant`
and the auth pair.

## Resources

| Resource | Path | Notable request fields |
| :--- | :--- | :--- |
| Companies | `/api/companies` | `name`*, `currency`*, `email`, `phone`, `address`, `tax_number`, `timezone` |
| Clients | `/api/clients` | `name`*, `company_name`, `email`, `phone`, `address`, `notes` |
| Projects | `/api/projects` | `title`*, `client_id`*, `status`*, `budget`*, `description`, dates, `manager_id`, `project_number` |
| Tasks | `/api/tasks` | `title`*, `project_id`*, `status`*, `priority`*, `description`, `assigned_user_id`, `due_date` |
| Cost codes | `/api/cost-codes` | `code`*, `name`*, `type`*, `parent_id` |
| Contracts | `/api/contracts` | `project_id`*, `title`*, `contract_value`*, `status`*, `signed_at` |
| BOQ items | `/api/boq-items` | `project_id`*, `description`*, `quantity`*, `unit`*, `unit_price`*, `cost_code_id`, `item_code` |
| Budgets | `/api/budgets` | `project_id`*, `type`*, `status`*, `forecast`, `items[]`* |
| Progress reports | `/api/progress-reports` | `project_id`*, `report_date`*, `status`*, `notes`, `items[]`* |
| Billings | `/api/billings` | `project_id`*, `period_start`*, `period_end`*, `status`*, `retention_rate`, `other_deductions`, `due_date`, `items[]`* |
| Daily logs | `/api/daily-logs` | `project_id`*, `log_date`*, `work_performed`*, `weather`, `crew_count`, `labor_hours`, `equipment_usage`, `issues`, `safety_incidents` |
| Project photos | `/api/project-photos` | `project_id`*, `category`*, `photo`* (file), `caption` |
| Documents | `/api/documents` | `project_id`*, `name`*, `category`*, `file`* (file) |
| Time entries | `/api/time-entries` | `project_id`*, `clock_in_at`*, `clock_out_at`, `break_minutes`, `latitude`, `longitude`, `notes` |
| Equipment | `/api/equipment` | `name`*, `type`*, `status`*, `service_due_at`, `notes` |
| Maintenance logs | `/api/maintenance-logs` | `equipment_id`*, `service_date`*, `description`*, `cost`, `currency` |
| Suppliers | `/api/suppliers` | `name`*, `email`, `phone`, `address`, `notes` |
| Expenses | `/api/expenses` | `project_id`*, `category`*, `amount`*, `date`*, `receipt` (file), `notes` |
| Change orders | `/api/change-orders` | `project_id`*, `description`*, `amount`* (may be negative), `status`*, `items[]` |
| Purchase orders | `/api/purchase-orders` | `project_id`*, `supplier_id`*, `status`*, `items[]`* |
| Invoices | `/api/invoices` | `project_id`*, `status`*, `issue_date`*, `due_date`*, `items[]`* |
| Estimates | `/api/estimates` | `title`*, `status`*, `lead_id`, `project_id`, `valid_until`, `tax_rate`, `items[]`* |
| Leads | `/api/leads` | `first_name`*, `last_name`*, `status`*, `email`, `phone`, `notes` |

`*` = required.

:::note File fields
`photo`, `file` and `receipt` are validated as uploads. The API expects multipart form data
for those two resources — everything else is JSON.
:::

## Line items

| Parent | Field | Line shape |
| :--- | :--- | :--- |
| Budgets | `items[]` | `description`, `quantity`, `unit`, `unit_price`, `cost_code_id?`, `boq_item_id?` |
| Progress reports | `items[]` | `description`, `quantity`, `percent_complete?`, `accomplished_amount`, `boq_item_id?`, `cost_code_id?` |
| Billings | `items[]` | `description`, `quantity`, `unit_price`, `boq_item_id?` |
| Estimates / Invoices / Purchase orders | `items[]` | `description`, `quantity` (integer), `unit_price` |
| Change orders | `items[]` | `category`, `description`, `amount` |

At least one line is required for budgets, progress reports, billings, estimates, invoices
and purchase orders.

## Enumerations

| Field | Values |
| :--- | :--- |
| Company `currency` | `USD`, `EUR`, `GBP`, `CAD`, `AUD`, `NZD`, `PHP` |
| Project `status` | `draft`, `pending`, `active`, `on_hold`, `completed`, `archived` |
| Task `status` | `to_do`, `in_progress`, `in_review`, `done` |
| Task `priority` | `low`, `medium`, `high`, `urgent` |
| Cost code `type` | `material`, `labor`, `equipment`, `subcontract`, `overhead`, `other` |
| Contract `status` | `draft`, `signed`, `completed`, `terminated` |
| Budget `type` | `original`, `revised` |
| Budget `status` | `draft`, `approved` |
| Progress report `status` | `draft`, `submitted`, `approved` |
| Billing `status` | `draft`, `submitted`, `under_review`, `approved`, `certified`, `due`, `partially_paid`, `paid` |
| Photo `category` | `before`, `during`, `after`, `inspection`, `safety` |
| Document `category` | `blueprint`, `cad`, `permit`, `contract`, `spec` |
| Equipment `type` | `heavy_machinery`, `tool`, `vehicle`, `other` |
| Equipment `status` | `available`, `in_use`, `maintenance`, `retired` |
| Expense `category` | `labor`, `fuel`, `materials`, `equipment`, `subcontract` |
| Change order `status` | `pending`, `approved`, `rejected` |
| Change order item `category` | `material`, `labor`, `equipment`, `overhead`, `profit`, `other` |
| Purchase order `status` | `draft`, `sent`, `approved`, `received` |
| Invoice `status` | `draft`, `sent`, `partial`, `paid`, `overdue` |
| Estimate `status` | `draft`, `sent`, `approved`, `rejected`, `converted` |
| Lead `status` | `new`, `contacted`, `qualified`, `lost`, `converted` |

## Special endpoints

### Record a billing payment

```http
POST /api/billings/{billing}/payments
```

```json
{
  "payment_date": "2026-04-05",
  "amount": 125000.00,
  "method": "bank_transfer",
  "reference": "BPI-88213",
  "notes": "Progress billing 3"
}
```

Returns the updated billing with `items` and `payments` loaded, including `paid_amount` and
`balance_amount`.

Route name is `api.api.billings.payments.store` (a pre-existing double prefix — harmless,
just don't be surprised when you see it in `route:list`).

### AI assistant

```http
POST /api/assistant
```

See [AI assistant endpoint](/docs/api/ai-assistant).

## Discovering routes

```bash
php artisan route:list --path=api --json
```
