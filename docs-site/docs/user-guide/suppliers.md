---
title: Suppliers
sidebar_position: 7
description: Keep the vendor directory used by purchase orders.
---

# Suppliers

Suppliers are the vendors you raise [purchase orders](/docs/user-guide/purchase-orders)
against. Require `manage_suppliers`.

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| Name | Yes | Trading name |
| Email | No | |
| Phone | No | |
| Address | No | |
| Notes | No | Payment terms, lead times, account references |

## Why keep them clean

- Only suppliers in the directory can be selected on a purchase order.
- The supplier list is per company; there is no cross-tenant sharing.
- Adding suppliers is part of the [first-run checklist](/docs/getting-started/first-run-checklist),
  because purchase orders unlock with the rest of the project modules.

## Related

- [Purchase orders](/docs/user-guide/purchase-orders)
- [Expenses](/docs/user-guide/expenses)
