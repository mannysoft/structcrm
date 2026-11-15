---
title: Expenses
sidebar_position: 23
description: Record actual project cost — the only thing that moves real margin.
---

# Expenses

Expenses are **actual cost**. They are the only input to `actual cost` in project
profitability, so anything missing here makes every project look more profitable than it is.

Requires `manage_expenses`.

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| Project | Yes | |
| Category | Yes | `labor`, `fuel`, `materials`, `equipment`, `subcontract` |
| Amount | Yes | In company currency |
| Date | Yes | Spend date |
| Receipt | No | Image, maximum 2 MB |
| Notes | No | Supplier, invoice number, justification |

## Categories

| Category | Use it for |
| :--- | :--- |
| `labor` | Crew wages, subcontractor day rates, overtime |
| `fuel` | Fuel, oil, lubricants |
| `materials` | Concrete, steel, timber, consumables |
| `equipment` | Plant hire, tool purchase, equipment maintenance |
| `subcontract` | Specialist subcontract packages |

Categories are fixed — there is no "other". Choose the closest fit and use **Notes** for
detail.

## Recording cost consistently

1. Record the expense when the cost is committed, not when the invoice is paid, if you
   want margins to reflect reality early.
2. Attach the receipt image — it is stored on the default filesystem disk with the amount.
3. Put the supplier and their invoice number in **Notes**; suppliers are not a field on
   expenses.
4. Record equipment maintenance spend twice if you track it: a
   [maintenance log](/docs/user-guide/equipment-and-maintenance) for the asset history and
   an expense for the project cost.

## What does not create expenses

| Data | Effect on cost |
| :--- | :--- |
| Time entries | None |
| Purchase orders | None |
| Maintenance logs | None |
| Daily logs | None |

That is by design (one source of truth), but it means you must post expenses yourself.
