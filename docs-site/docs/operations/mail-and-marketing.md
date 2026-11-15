---
title: Mail and marketing
sidebar_position: 4
description: Mailers, queued notifications, consent handling and campaign delivery.
---

# Mail and marketing

## Mailers

| `MAIL_MAILER` | Use |
| :--- | :--- |
| `log` | Development default — mails are written to `storage/logs/laravel.log` |
| `smtp` | Any SMTP provider |
| `resend` | Production, requires `RESEND_API_KEY` |
| `array` | Tests |

Transactional mail in StructCRM:

| Mail | Trigger | Queued |
| :--- | :--- | :--- |
| Staff invitation | Team invite | Yes |
| Client portal invitation | Client portal invite | Yes |

Both use signed URLs that expire in 7 days, built from `APP_URL`. A wrong `APP_URL` produces
links pointing at `localhost`.

## Local testing

```dotenv
MAIL_MAILER=log
QUEUE_CONNECTION=sync
```

With `sync`, mail is sent inside the request and appears immediately in the log — the fastest
loop for invitation work.

## Deliverability basics

1. Set a real `MAIL_FROM_ADDRESS` on a domain you control.
2. Verify the sending domain with your provider (SPF, DKIM, DMARC).
3. Keep transactional and marketing streams separate if possible.
4. Monitor bounces — the application does not process them.

## Marketing consent

| Column | Meaning |
| :--- | :--- |
| `users.marketing_opt_in` | Boolean; only `true` users receive campaigns |
| `users.marketing_consented_at` | Start of the delay clock and the audit trail |

Consent is captured on the registration form. Never seed or import users with consent you
cannot evidence.

## Campaign delivery

`marketing:send-due-emails` runs daily at 09:00 through the scheduler and:

1. Iterates active campaigns and their steps.
2. Selects users with `marketing_opt_in = true` whose `marketing_consented_at` is at least
   `delay_days` in the past.
3. Skips users already logged for that step.
4. Sends and writes `marketing_email_logs`.

:::danger[Platform-wide]
The query is not scoped by company. Every opted-in user in the database is eligible for every
active campaign, regardless of tenant. Use this module for product-level announcements, not
per-tenant marketing.
:::

## Unsubscribe

Every marketing email carries a signed link to `/marketing/unsubscribe/{user}`, which flips
`marketing_opt_in` to `false`. The route is signed — a forged id returns 403.

## Resend contact sync

When `RESEND_API_KEY` and `RESEND_SEGMENT_ID` exist, the `Registered` listener queues
`SyncResendContactJob` for opted-in users. This needs a worker; without one, contacts never
reach Resend.

## Checklist for turning marketing on

- [ ] `MAIL_MAILER=resend` and `RESEND_API_KEY` set
- [ ] Sending domain verified
- [ ] `MAIL_FROM_ADDRESS` set to the verified domain
- [ ] Queue worker running
- [ ] Scheduler cron installed
- [ ] At least one active campaign with steps
- [ ] Unsubscribe link tested end to end
