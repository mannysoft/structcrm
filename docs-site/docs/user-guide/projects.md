---
title: Projects
sidebar_position: 8
description: Create and manage the project records every other module hangs from.
---

# Projects

A project is the hub of StructCRM: contracts, BOQ items, budgets, progress reports,
billings, invoices, expenses, change orders, purchase orders, tasks, daily logs, photos,
documents and time entries all belong to one.

Requires `manage_projects` to create or edit. Creating a project requires an existing
[client](/docs/user-guide/clients).

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| Title | Yes | Short project name shown everywhere |
| Client | Yes | Must belong to your company |
| Description | No | Scope summary |
| Status | Yes | `draft`, `pending`, `active`, `on_hold`, `completed`, `archived` |
| Budget | Yes | Target project budget in your company currency (defaults to 0) |
| Start date / End date | No | End must be on or after start |
| Project manager | No | Any staff user in your company |
| Project number | No | Leave blank to auto-generate `PRJ-<year>-<seq>` |

## Statuses

| Status | Use it when |
| :--- | :--- |
| `draft` | Scoping the job, not awarded yet |
| `pending` | Awarded but not started |
| `active` | Work in progress — the normal state |
| `on_hold` | Paused (weather, client instruction, dispute) |
| `completed` | Work finished, final account closing |
| `archived` | Closed and hidden from day-to-day lists |

Only `draft`, `pending`, `active` and `on_hold` count as **active projects** on the
[dashboard](/docs/user-guide/dashboard).

## The project-only view

Select a project in the top-bar project switcher and every project-scoped list — BOQ,
budgets, billings, change orders, contracts, daily logs, documents, estimates, expenses,
invoices, photos, progress reports, purchase orders, tasks, time entries — is filtered to
that project. Create forms on those screens also default to it.

Clear the switcher to see everything again.

## Practical order of operations

```text
Project → Contract → Cost codes → BOQ → Budget
        → Progress report → Progress billing → Payments
        → Invoices / Expenses / Change orders / Purchase orders
```

You can create records in any order, but the financial KPIs only become meaningful once
the contract, budget and expenses exist.

## Deleting a project

Deleting is soft. Anything pointing at the project (photos, logs, invoices) becomes hard
to reach from the UI, so prefer setting the status to `archived` once work is finished.
