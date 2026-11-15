---
title: Estimates
sidebar_position: 19
description: Price prospective work and carry the numbers into a project.
---

# Estimates

Estimates are the sales-side quote: line items, tax, validity date, and a status that
tracks the conversation with the client. Requires `manage_estimates`.

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| Title | Yes | What is being priced |
| Status | Yes | See below |
| Lead | No | Link a pre-sale [lead](/docs/user-guide/leads) |
| Project | No | Link a live project |
| Valid until | No | Quote expiry |
| Tax rate (%) | No | 0–100, applied to the subtotal |
| Items | Yes (at least one) | Description, quantity (whole number ≥ 1), unit price |

The estimate number is generated as `EST-<seq>`.

## Statuses

| Status | Meaning |
| :--- | :--- |
| `draft` | Being built |
| `sent` | Issued to the client |
| `approved` | Client accepted |
| `rejected` | Client declined |
| `converted` | Became a project (or a project's contract) |

## Totals

```text
subtotal   = Σ (quantity × unit price)
tax amount = subtotal × tax rate
amount     = subtotal + tax amount
```

Totals are recalculated on save from the line items — never type a total.

## From estimate to project

There is no one-click conversion. The intended flow is:

1. Price the work as an estimate linked to the **lead**.
2. Once approved, set the status to `approved`.
3. Create the [client](/docs/user-guide/clients) and [project](/docs/user-guide/projects).
4. Re-create the winning lines as a [contract](/docs/user-guide/contracts) and a
   [BOQ](/docs/user-guide/bill-of-quantities).
5. Mark the estimate `converted` and the lead `converted`.

:::caution Quantity is a whole number here
Estimate and invoice line quantities are validated as integers. Use a small unit (e.g.
"each" instead of "sq.m.") if you need fractional quantities, or round up.
:::
