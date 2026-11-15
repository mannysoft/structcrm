---
title: Client portal
sidebar_position: 27
description: What external clients can see and do in their dedicated login.
---

# Client portal

The portal is a separate login for your clients at `/portal`. It uses its own `client`
guard, so staff and client sessions never mix.

## Giving a client access

1. Open the client record: **Clients → (client) → Edit**.
2. Under **Portal access**, enter the client's email and submit.
3. A queued email with a signed, 7-day link to `/portal/set-password/{user}` is sent.
4. The client sets a password and signs in at `/portal/login`.

The client user is linked to exactly one client record; everything they see is scoped to
it.

## What clients see

| Section | Contents |
| :--- | :--- |
| **Projects** | Their projects with status and photo counts |
| **Pending change orders** | Change orders awaiting their decision, with approve/reject actions |
| **Open invoices** | Invoices that are `draft`, `sent`, `partial` or `overdue`, with a "mark as paid" action |

## What clients cannot do

- See other clients' projects, or any other company's data.
- See BOQ, budgets, expenses, margins or any internal commercial data.
- Use the AI assistant (it is staff-only).
- Access staff routes — the guards are separate and staff pages require the `web` guard.

## Approving change orders

A client approving a change order runs the same action as an internal approval: status
becomes `approved`, and a revised budget is created. Verify the client's decision before
you bill against it, and check the revised budget afterwards.

## "Mark as paid"

The client marking an invoice paid sets its status to `paid` directly. There is no payment
record behind it — no amount, date or reference. Reconcile against your bank statement
before treating the invoice as settled.

## Practical onboarding

1. Get the client's billing email right — the invitation goes there.
2. Explain that approving a change order is a contractual decision.
3. Tell them the "mark paid" button is a notification, and that your receipt is the formal
   acknowledgement.
