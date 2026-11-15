---
title: Dashboard
sidebar_position: 3
description: What each KPI on the dashboard measures and how it is calculated.
---

# Dashboard

The dashboard answers one question: **is this company making money on its projects, and
where is cash stuck?**

## KPIs

| KPI | Definition |
| :--- | :--- |
| **Active projects** | Projects whose status is `draft`, `pending`, `active` or `on_hold` |
| **Total contract value** | Sum of `contract_value` across all contracts |
| **Outstanding billing** | Total net billed minus total payments received |
| **Overdue collections** | Unpaid remainder of billings whose due date is in the past |
| **Projected profit** | Sum of projected profit across all projects |
| **Total cost variance** | Sum of (revised budget − projected final cost); positive means budget remaining |
| **Projects at risk** | The five projects with the lowest projected margin |

## How profitability is calculated

Profitability is not a stored figure — it is derived per project:

```text
revised contract value = original contract value
                       + approved change orders
actual cost            = sum of expenses
forecast to complete   = current budget forecast, or (revised budget − actual cost) if none
projected final cost   = actual cost + forecast to complete
projected profit       = revised contract value − projected final cost
projected margin (%)   = projected profit ÷ revised contract value × 100
cost variance          = revised budget − projected final cost
```

Consequences worth knowing:

- **Only approved change orders** move revenue. Pending or rejected change orders are ignored.
- **Only approved/current budgets** move cost. The latest budget created for a project is
  marked current automatically.
- **Only expenses** count as actual cost. Time entries and purchase orders do not post to
  expenses automatically, so record actual spend as expenses for accurate margins.

## Getting started checklist

Until every setup step is complete, the eight-step checklist appears on the dashboard and
in the **Administration** navigation group. See
[First-run checklist](/docs/getting-started/first-run-checklist).

## Related

- [Budgets](/docs/user-guide/budgets)
- [Change orders](/docs/user-guide/change-orders)
- [Progress billings](/docs/user-guide/progress-billings)
