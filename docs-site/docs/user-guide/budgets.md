---
title: Budgets
sidebar_position: 18
description: Version your cost plan, set the forecast to complete, and drive profit reporting.
---

# Budgets

A budget is the cost plan for a project, made of line items. Versioning and the
"current" flag make the budget the source of cost truth for profitability.

Requires `manage_budgets`.

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| Project | Yes | |
| Type | Yes | `original` or `revised` |
| Status | Yes | `draft` or `approved` |
| Forecast to complete | No | How much you still expect to spend; defaults to revised budget − actual cost |
| Items | Yes (at least one) | Description, quantity, unit, unit price, optional cost code and BOQ item |

## Versions and the current budget

Every save creates a **new** budget row:

- `version` = highest existing version for the project + 1.
- `is_current = true` on the new row; all older budgets are flipped to `false`.
- Older rows are kept — history is never overwritten.

Profitability reads the **current** budget's forecast, or falls back to
`revised budget − actual cost` when no forecast is set.

```text
revised budget      = sum of every budget line total for the project
forecast to complete = current budget's forecast (or revised budget − actual cost)
projected final cost = actual cost (expenses) + forecast to complete
```

## Original vs revised

| Type | Use it for |
| :--- | :--- |
| `original` | The approved baseline, frozen |
| `revised` | Re-baselines after scope change |

Approving a [change order](/docs/user-guide/change-orders) automatically creates an
**approved, revised** budget containing the change order amount. That is intentional: the
cost plan follows the revenue change.

## Forecast discipline

The forecast is the only place you can tell StructCRM what you *expect* to spend versus
what you *have* spent. Update it monthly, immediately after the progress report:

1. Create the current budget with your cost lines.
2. Each month, create a new revision with an updated **forecast to complete**.
3. The dashboard's projected profit and variance update accordingly.

## Related

- [Cost codes](/docs/user-guide/cost-codes)
- [Dashboard](/docs/user-guide/dashboard)
- [Change orders](/docs/user-guide/change-orders)
