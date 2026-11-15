---
title: Invoices
sidebar_position: 22
description: Issue simple invoices with line items and track them to payment.
---

# Invoices

Invoices are the straightforward, line-item billing document. Use
[progress billings](/docs/user-guide/progress-billings) for measured/certified work and
invoices for everything else (deposits, small jobs, service calls).

Requires `manage_invoices`.

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| Project | Yes | |
| Status | Yes | `draft`, `sent`, `partial`, `paid`, `overdue` |
| Issue date | Yes | |
| Due date | Yes | Must be on or after the issue date |
| Lines | Yes (at least one) | Description, quantity (whole number), unit price |

The invoice number is generated as `INV-<seq>`, and the total is
`Σ (quantity × unit price)` — recalculated on every save.

## Statuses

| Status | Meaning |
| :--- | :--- |
| `draft` | Not yet issued |
| `sent` | Issued to the client |
| `partial` | Partly paid |
| `paid` | Settled |
| `overdue` | Past due and unpaid |

There is no automatic status transition: no scheduled job flips invoices to `overdue`, and
no payment allocation changes the status. Update it manually when you reconcile.

## Client payments

A client can mark an invoice as **paid** from the [client portal](/docs/user-guide/client-portal).
That sets the status to `paid` immediately — treat it as the client's claim, and confirm
against your bank before closing the month.

## Limits to know

- No PDF export and no email sending for invoices yet.
- Quantities are integers.
- There is no tax field on invoices (tax lives on estimates and progress billings).
