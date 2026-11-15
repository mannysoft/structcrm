---
title: Purchase orders
sidebar_position: 25
description: Order materials from suppliers and track the order lifecycle.
---

# Purchase orders

Purchase orders formalise material and service orders to a
[supplier](/docs/user-guide/suppliers). Requires `manage_purchase_orders`.

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| Project | Yes | |
| Supplier | Yes | Must belong to your company |
| Status | Yes | `draft`, `sent`, `approved`, `received` |
| Lines | Yes (at least one) | Description, quantity (whole number), unit price |

The PO number is generated as `PO-<seq>`, and the total is `Σ (quantity × unit price)`.

## Statuses

| Status | Meaning |
| :--- | :--- |
| `draft` | Being prepared |
| `sent` | Issued to the supplier |
| `approved` | Confirmed by you (or the supplier) |
| `received` | Goods/services delivered |

## Purchase orders are commitments, not costs

A purchase order does **not** create an expense and does not affect project margin. When
the delivery arrives:

1. Move the PO to `received`.
2. Record an [expense](/docs/user-guide/expenses) with category `materials` (or the relevant
   category) for the actual invoiced amount.

This keeps commitment (PO) and actual cost (expense) separate — which is what you want when
supplier prices change between order and delivery.

## Limits

- No PDF or email dispatch for purchase orders.
- No partial receipts or back orders.
- Quantities are integers.
