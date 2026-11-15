---
title: Progress billings
sidebar_position: 21
description: Bill measured work with retention and deductions, and record payments against it.
---

# Progress billings

Progress billing is the construction-specific invoice: gross value of work this period,
less retention and other deductions, less what you already billed, equals the net amount
due.

Requires `manage_billings`; recording payments requires `manage_payments`.

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| Project | Yes | |
| Progress report | No | Link the [report](/docs/user-guide/progress-reports) being billed |
| Period start / end | Yes | Billing period |
| Status | Yes | See below |
| Retention rate (%) | No | Enter `10` for 10% — stored as a fraction |
| Other deductions | No | Amount in company currency |
| Due date | No | Drives overdue collections |
| Notes | No | |
| Lines | Yes (at least one) | BOQ item (optional), description, quantity, unit price |

The billing number is generated as `BIL-<seq>`.

## How the amounts are calculated

```text
gross        = Σ (quantity × unit price)
retention    = gross × retention rate
previous     = Σ net_amount of every other billing on the project
net amount   = gross − retention − other deductions
```

`previous billing` is stored for reference but is **not** subtracted from `net amount` —
the net shown is this period's invoice value. Do not also deduct previous billings in
**Other deductions**, or you will double-count.

When you save a billing with a status that implies certification (`certified`, `due`,
`partially_paid`, `paid`), `certified_at` is stamped automatically.

## Statuses

| Status | Meaning |
| :--- | :--- |
| `draft` | Being prepared — not yet issued |
| `submitted` | Sent to the client/consultant |
| `under_review` | Being checked/certified |
| `approved` | Quantity approved |
| `certified` | Certified for payment (stamps `certified_at`) |
| `due` | Payment now expected |
| `partially_paid` | Some payments recorded |
| `paid` | Fully settled |

Nothing enforces the order; the status is a label you maintain. Keep it honest — payments
and overdue reporting both read it.

## Recording payments

Open the billing and use the **Payments** panel on the edit screen. Each payment records:

| Field | Required | Notes |
| :--- | :--- | :--- |
| Payment date | Yes | |
| Amount | Yes | In company currency |
| Method | No | e.g. bank transfer, cheque |
| Reference | No | Bank reference / OR number |
| Notes | No | |

Payments are separate rows, so partial payments are supported. Outstanding billing on the
[dashboard](/docs/user-guide/dashboard) is `Σ net amount − Σ payments`.

:::tip[Overdue collections]
A billing counts as overdue when it has a `due date` in the past **and** an unpaid
remainder. Setting a due date is therefore the single most useful thing you can do for
cash-flow visibility.
:::
