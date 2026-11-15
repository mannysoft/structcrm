---
title: Overview
sidebar_position: 1
description: How StructCRM is organised, how navigation works, and the patterns every screen shares.
---

# User guide overview

StructCRM is organised around a **company** (your tenant). Every client, project,
invoice and time entry you create belongs to that company, and you can never see another
company's data — even when several companies share the same database.

## Navigation

The sidebar groups modules by the way construction businesses actually work:

| Group | Modules |
| :--- | :--- |
| **Overview** | Dashboard, AI Assistant |
| **Projects & Operations** | Projects, Tasks, Daily Logs, Site Photos, Documents, Time Tracking, Fleet & Equipment, Maintenance Logs, Progress Reports |
| **Commercial & Finance** | Contracts, BOQ, Budgets, Estimates, Progress Billings, Cost Codes, Invoices, Expenses, Change Orders, Purchase Orders |
| **Sales** | Clients, Leads, Suppliers |
| **Administration** | Company, Team (needs `manage_users`), Marketing (needs `manage_marketing`), Getting Started (while the checklist is incomplete) |

Every module page lists records for **your company**, newest first, with search and
pagination (15 rows per page).

## The project switcher

The top bar has a project selector. Selecting a project stores it in your session and
many "create" screens default to it. It is a convenience, not a filter — list pages still
show every project you have access to.

## Patterns shared by every screen

| Pattern | What to expect |
| :--- | :--- |
| **List pages** | A search box (`?search=` in the URL, so results are shareable), a table with the handful of columns that matter, and row actions for edit/delete |
| **Create/edit forms** | Validation errors appear inline below each field; successful saves show a green status banner |
| **Deletes** | Soft-deleted where the model supports it, behind a confirmation dialog |
| **Document numbers** | Generated automatically per company on first save (see below) |
| **Status badges** | Colour-coded (e.g. green for paid/approved, amber for pending) |
| **Money** | Always shown in your company's currency with two decimals |

### Generated document numbers

| Document | Format | Example |
| :--- | :--- | :--- |
| Project | `PRJ-<year>-<seq>` | `PRJ-2026-001` |
| Contract | `CON-<seq>` | `CON-1` |
| Progress report | `REP-<seq>` | `REP-1` |
| Progress billing | `BIL-<seq>` | `BIL-1` |
| Estimate | `EST-<seq>` | `EST-1` |
| Invoice | `INV-<seq>` | `INV-1` |
| Change order | `CO-<seq>` | `CO-1` |
| Purchase order | `PO-<seq>` | `PO-1` |

Sequences are per company and continue across soft deletes.

## Why some buttons say "you are not allowed"

Navigation is not permission-filtered, but every action is. If you can see a module but
cannot create or edit in it, your role is missing the matching `manage_*` permission. The
[roles and permissions](/docs/user-guide/roles-and-permissions) page maps roles to
modules, and an administrator can change your role from **Team**.

## A sensible first week

1. Finish the [first-run checklist](/docs/getting-started/first-run-checklist).
2. Record real clients, projects and contracts.
3. Import the bill of quantities from Excel.
4. Set up the current budget so profitability reporting is meaningful.
5. Start daily logs and site photos in the field.
6. Move to the monthly rhythm: progress report → progress billing → payments.

## Related

- [Roles and permissions](/docs/user-guide/roles-and-permissions)
- [Dashboard](/docs/user-guide/dashboard)
- [Client portal](/docs/user-guide/client-portal)
