---
title: Clients
sidebar_position: 5
description: Create client records, keep CRM notes, and give a client access to the portal.
---

# Clients

Clients are the root of the commercial chain: **client → project → contract → BOQ →
billing**. You cannot create a project without a client.

Requires `manage_clients` to create, edit or delete.

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| Name | Yes | Person or organisation name |
| Company name | No | Legal entity, if different from the contact |
| Email | No | Used as the default portal invitation address |
| Phone | No | |
| Address | No | Billing/site address |
| Notes | No | Free-text CRM notes: preferences, history, payment behaviour |

## Creating a client

**Sales → Clients → New client**. After saving, open the record to see its projects and
portal access.

## Portal access

Open **Clients → (client) → Edit** and use the **Portal access** panel:

1. Enter the email address to invite.
2. Submit — a client user is created (or updated) and receives a queued email with a
   signed 7-day link to `/portal/set-password/{user}`.
3. The client sets a password and can sign in at `/portal/login`.

Portal users see only that client's projects, pending change orders and open invoices.
See [Client portal](/docs/user-guide/client-portal).

:::warning
`InviteClientUserAction` re-points an existing user account to the client and flips it to
`type = client`. Do not "invite" an email that belongs to one of your staff.
:::

## Deleting

Client deletion is soft: the record is hidden but history (projects, invoices) remains
intact for reporting. Deleting a client with active projects is possible but will make
those projects awkward to navigate — archive projects first.

## Related

- [Leads](/docs/user-guide/leads)
- [Projects](/docs/user-guide/projects)
- [Client portal](/docs/user-guide/client-portal)
