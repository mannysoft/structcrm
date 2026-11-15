---
title: Change orders
sidebar_position: 24
description: Manage scope and value changes, including client approval and automatic budget revision.
---

# Change orders

A change order records a scope change, its value, and whether the client accepted it.
Approval has real consequences: it adds to revised contract value **and** creates a revised
budget.

Requires `manage_change_orders`.

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| Project | Yes | |
| Description | Yes | Scope change |
| Amount | Yes | In company currency; **negative values are allowed** for omissions |
| Status | Yes | `pending`, `approved`, `rejected` |
| Lines | No | Category, description, amount |

The change order number is generated as `CO-<seq>`.

Line categories: `material`, `labor`, `equipment`, `overhead`, `profit`, `other`.

## What happens on approval

```text
status        → approved
approved_at   → now
new budget    → created (type: revised, status: approved)
                lines = the change order's lines
                (or a single "Change order CO-n" line from the amount)
```

That new budget becomes the **current** budget for the project, so projected cost and
profit reflect the change immediately. Rejection clears `approved_at` and leaves budgets
untouched.

:::caution Re-approving
Approving the same change order twice creates a second revised budget — the value is added
twice. The UI normally prevents this by only offering the action on pending orders; the AI
assistant asks for confirmation for the same reason.
:::

## Client approval

Clients approve or reject pending change orders from the
[client portal](/docs/user-guide/client-portal). That is genuinely the client's decision —
you do not need to re-enter it, but check the status before billing.

## Recommended workflow

1. Raise the change order as `pending` with the value breakdown in the lines.
2. Notify the client (the portal shows it in their pending list).
3. Client approves → status becomes `approved`.
4. Confirm the revised budget looks right.
5. Bill the change order value through
   [progress billings](/docs/user-guide/progress-billings) or
   [invoices](/docs/user-guide/invoices) at the agreed milestone.
