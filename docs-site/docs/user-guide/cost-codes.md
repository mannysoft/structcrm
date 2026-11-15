---
title: Cost codes
sidebar_position: 16
description: Build the work-type hierarchy that BOQ items, budgets and reports are grouped by.
---

# Cost codes

Cost codes are a company-wide chart of accounts for construction work: labour, materials,
equipment, subcontracts, overhead. BOQ items, budgets and progress reports can all point
at one.

Requires `manage_cost_codes`.

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| Code | Yes | Unique per company, up to 50 characters (e.g. `100`, `100.10`) |
| Name | Yes | Human label, e.g. "Site preparation" |
| Type | Yes | `material`, `labor`, `equipment`, `subcontract`, `overhead`, `other` |
| Parent code | No | Another cost code in your company, for hierarchy |

Codes are unique per company **among non-deleted rows**, so a soft-deleted code can be
reused.

## Suggested structure

```text
100  Site works
  100.10  Clearing and grubbing
  100.20  Excavation
200  Concrete
  200.10  Formwork
  200.20  Reinforcement
300  Masonry
900  Overheads
```

Keep the hierarchy shallow — two levels is enough for reports to stay readable.

## Where cost codes appear

| Screen | How cost codes are used |
| :--- | :--- |
| BOQ | Optional tag per line item |
| BOQ import | Matched by **code** from the spreadsheet's `cost_code` column |
| Budgets | Optional tag per budget line |
| Progress reports | Optional tag per progress line |

Unknown codes in an import resolve to `null` rather than failing the import — check the
imported rows afterwards.

## Adding codes is step 2 of the checklist

Cost codes come before clients in the
[first-run checklist](/docs/getting-started/first-run-checklist) because every later
financial record can reference them.
