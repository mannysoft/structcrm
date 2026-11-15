---
title: Marketing campaigns
sidebar_position: 26
description: Build sequenced email campaigns for opted-in users, and understand what actually gets sent.
---

# Marketing campaigns

The marketing module sends **consent-based** email sequences to people who opted in when
they registered. Requires `manage_marketing` (owner and administrator have it).

:::warning Scope
Campaigns are **platform-wide**, not per-tenant. The sending command selects every
`users` row with `marketing_opt_in = true` in the database, regardless of company. Treat
this as an operator feature (product announcements, onboarding), not a tenant CRM tool.
:::

## Campaign fields

| Field | Notes |
| :--- | :--- |
| Name | Internal name, e.g. "Trial onboarding" |
| Description | Purpose |
| Active | Only active campaigns send |
| Steps | The sequence |

## Steps

Each step is one email:

| Field | Notes |
| :--- | :--- |
| Subject | Email subject |
| Body | Message body |
| Delay (days) | Days **after the user's consent date** before this step is due |
| Sort order | Display order in the editor |

```text
consent + delay_days ≤ today  →  step is due
```

## Sending

A scheduled command sends due emails every day at **09:00** (application timezone):

```bash
php artisan marketing:send-due-emails
```

It:

1. Loads every active campaign and its steps.
2. Finds opted-in users whose consent date is at least `delay_days` ago.
3. Skips anyone who already received that step (deduplicated in `marketing_email_logs`).
4. Sends the email and writes a log row with the subject and send time.

Requires a running scheduler (`php artisan schedule:work` locally) and working mail
configuration. With `MAIL_MAILER=log`, "sent" emails only reach the log file.

## Consent and unsubscribing

- Consent is captured at registration (`marketing_opt_in`, `marketing_consented_at`).
- Every marketing email includes a **signed** unsubscribe link
  (`/marketing/unsubscribe/{user}`) that flips the user's opt-in off.
- Users who never opted in are never emailed.

## Resend integration

If `RESEND_API_KEY` and `RESEND_SEGMENT_ID` are configured, opt-in events queue
`SyncResendContactJob` to keep the Resend audience in sync. This requires a queue worker.

## Email log

The campaigns screen shows the number of steps per campaign; the log table records who
received which step and when. Use it to answer "did this person get the third email?".
