---
title: Contracts
sidebar_position: 15
description: Record the original contract value that every margin calculation is measured against.
---

# Contracts

A contract records the agreed value of a project. It is the revenue anchor: projected
profit and margin on the [dashboard](/docs/user-guide/dashboard) are computed from the
contract value plus approved change orders.

Requires `manage_contracts`.

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| Project | Yes | |
| Title | Yes | e.g. "Main works contract" |
| Contract value | Yes | In company currency; zero is allowed |
| Status | Yes | `draft`, `signed`, `completed`, `terminated` |
| Signed at | No | Date of signature |

The contract number is generated automatically as `CON-<seq>`.

## One contract per project?

The dashboard and profitability service read the **first** contract for a project. If you
create several contracts for one project, only one is used in margin calculations — keep a
single "main" contract per project and record variations as
[change orders](/docs/user-guide/change-orders).

## Statuses

| Status | Meaning |
| :--- | :--- |
| `draft` | Being negotiated |
| `signed` | Executed — the normal state for live work |
| `completed` | Obligations discharged |
| `terminated` | Ended early |

Status does not affect the financial calculations; the value counts even while `draft`. Get
the number right before you rely on margins.

## Revenue maths

```text
revised contract value = contract value + approved change orders
```

Rejected and pending change orders are excluded. See
[Change orders](/docs/user-guide/change-orders).
