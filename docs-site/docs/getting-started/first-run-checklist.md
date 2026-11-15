---
title: First-run checklist
sidebar_position: 6
description: The eight setup steps StructCRM asks for before it unlocks creating records, and why each one matters.
---

# First-run checklist

A brand-new company lands on a **Getting started** page (`/getting-started`) and the
dashboard shows the same checklist. It exists because almost every record in StructCRM
depends on a project, and a project depends on a client.

## The eight steps

| # | Step | What it unlocks |
| :--- | :--- | :--- |
| 1 | Complete your company profile | Email, phone, address, tax number and timezone appear on invoices, contracts and reports |
| 2 | Add cost codes | Group BOQ items, budgets and expenses by work type |
| 3 | Add your first client | Required before a project can exist |
| 4 | Add your first supplier | Required for purchase orders and expense provenance |
| 5 | Create your first project | Unlocks every project-scoped module |
| 6 | Add your first contract | The original value that budgets, billings and profitability are measured against |
| 7 | Add your first BOQ items | The scope of work you bill progress against |
| 8 | Create your first invoice | Bills completed work and starts cash-flow tracking |

The page shows progress as a percentage and always highlights the next step.

## The route gate

Steps are not just suggestions. Create routes are protected by the
`getting-started` middleware (`EnsureGettingStartedPrerequisites`), which redirects back
to the checklist with an explanation when a prerequisite is missing.

| Route | Requires |
| :--- | :--- |
| `projects.create` | Clients |
| `contracts.create`, `boq_items.create`, `boq_items.import`, `budgets.create`, `billings.create`, `invoices.create`, `change_orders.create`, `expenses.create`, `tasks.create`, `daily_logs.create`, `progress_reports.create`, `project_photos.create`, `documents.create`, `time_entries.create`, `purchase_orders.create` | Projects |

## Hiding and restoring the checklist

The checklist can be dismissed per company; the state is stored in
`companies.getting_started_dismissed_at`. Dismissing only hides it — the gate still
applies. Restore it from the Getting started page at any time.

## Practical order of operations

```text
Company profile → Cost codes → Clients → Suppliers
                → Projects → Contracts → BOQ → Budget
                → Progress report → Progress billing → Payments
```

Budgets are optional in the checklist but required before
[profitability reporting](/docs/user-guide/dashboard) becomes meaningful, and progress
billings are normally driven by a progress report.

## Next step

[Open the User Guide →](/docs/user-guide/overview)
