---
title: BOQ
sidebar_position: 17
description: Build the bill of quantities manually or import it from Excel, and keep it as the billing backbone.
---

# BOQ (Bill of Quantities)

The BOQ is the priced scope of work. Progress reports measure against it and progress
billings reference it, so a clean BOQ is the single biggest quality lever in StructCRM.

Requires `manage_boq`.

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| Project | Yes | |
| Cost code | No | Must belong to your company |
| Item code | No | Your own numbering, e.g. `1001` |
| Description | Yes | |
| Quantity | Yes | Decimal allowed |
| Unit | Yes | Free text: `cu.m.`, `kg`, `sq.m.`, `lot` |
| Unit price | Yes | In company currency |

`Total` is computed as `quantity × unit price` and stored in minor units.

## Importing from Excel

**BOQ → Import** (also available from the project checklist) accepts an Excel or CSV file
and imports rows for one project.

### Expected columns

The first row is treated as headings and must match these names:

| Column | Required | Notes |
| :--- | :--- | :--- |
| `description` | Yes | Rows with a blank description are skipped |
| `quantity` | Yes | Numeric |
| `unit_price` | Yes | In company currency, converted to minor units |
| `item_code` | No | |
| `unit` | No | Defaults to `lot` |
| `cost_code` | No | Matched against the cost code **code**, not its name |

Download the sample template from the import screen
(`GET /boq-items/template`, a CSV with three example rows).

### Import behaviour

- Import runs in a single transaction — a hard failure rolls everything back.
- Rows without a description are silently skipped.
- Unmatched cost codes become `null`; nothing fails.
- Nothing is deduplicated: importing the same file twice doubles the BOQ.

:::warning[Check the count]
The import reports how many rows were created. Compare it with your spreadsheet before
billing anything.
:::

## Working the BOQ

1. Import the priced BOQ once the contract is signed.
2. Review totals against the contract value — they should be close.
3. Tag cost codes so budgets and reports can be grouped.
4. From then on, measure progress against these lines in
   [progress reports](/docs/user-guide/progress-reports) rather than editing the BOQ.
5. Add scope changes as [change orders](/docs/user-guide/change-orders), not as silent BOQ
   edits.

## Filtering

Selecting a project in the top bar filters the BOQ to that project.
