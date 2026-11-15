---
title: Company and team
sidebar_position: 4
description: Manage your company profile, currency and timezone, and invite staff with the right roles.
---

# Company and team

## Company profile

**Administration → Company** shows your tenant details; **Company settings** edits them.

| Field | Why it matters |
| :--- | :--- |
| **Name** | Shown to the AI assistant and in documents |
| **Email / phone / address** | Appear on invoices, contracts and reports |
| **Tax number** | VAT/GST/EIN printed on documents |
| **Timezone** | Determines "today" for overdue calculations and for the AI assistant |
| **Currency** | Currency used for all money stored and displayed (`USD`, `EUR`, `GBP`, `CAD`, `AUD`, `NZD`, `PHP`) |
| **Logo** | Uploaded to the public disk and shown in the app |

Changing the currency does **not** convert existing amounts — it changes how new records
are stamped and how amounts are labelled. Set it before entering data.

You need `manage_company` to edit the profile. Completing the profile is the first step of
the [first-run checklist](/docs/getting-started/first-run-checklist).

## Team

**Administration → Team** (requires `manage_users`) lists the staff in your company and
lets you invite people.

### Inviting a team member

1. Enter the email address and choose a role.
2. Submit — the user is created (or re-pointed, if that email already exists) with a
   random password and the chosen role.
3. A queued email is sent with a **signed link valid for 7 days** to
   `/staff/set-password/{user}`.
4. The recipient chooses a password and is signed in.

:::note Mail delivery
Invitations are queued. Without a queue worker (or with `MAIL_MAILER=log`) the email never
arrives — check `storage/logs/laravel.log` in development, or run `composer run dev`.
:::

The invitation email subject currently says *"You have been invited to ConstructFlow"* —
legacy phrasing that does not affect functionality.

### Re-inviting

Inviting an email that already exists updates that user's company and role and sends a
fresh link. If you change someone's role later, use the row menu rather than re-inviting.

### Role rules

- You cannot change your own role.
- Only owners can assign the `owner` role.
- Non-owners do not see `owner` in the list.

See [Roles and permissions](/docs/user-guide/roles-and-permissions).

## Company switching

There is no company switcher: one user belongs to one company. To work with a second
company (for example an affiliate), use a separate account or a separate deployment.
