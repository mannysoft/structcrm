---
title: Configuration
sidebar_position: 3
description: Environment variables that matter, and the defaults that ship with StructCRM.
---

# Configuration

StructCRM is configured through `.env` and standard Laravel config files in `config/`.
The full variable reference lives in [Operations → Environment variables](/docs/operations/environment-variables);
this page covers the choices you actually make on day one.

## Application

```dotenv
APP_NAME=StructCRM
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=UTC
```

`APP_URL` is used to build signed URLs (staff and client invitation links, unsubscribe
links), so set it correctly before inviting anybody.

Company-level timezone and currency live in the database (`companies.timezone`,
`companies.currency`) and override the app defaults for the assistant and for display.
Supported currencies: `USD`, `EUR`, `GBP`, `CAD`, `AUD`, `NZD`, `PHP`.

## Data stores

The defaults keep everything in one place and require no extra services:

```dotenv
DB_CONNECTION=sqlite
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Switch to MySQL for production (see [Installation](/docs/getting-started/installation#database-choice)).
Redis is supported by Laravel but not required or configured by the app.

:::warning[Changing the queue driver]
If you move to `QUEUE_CONNECTION=redis` or `sync`, update your worker and deployment
process accordingly. On `sync`, queued mail (invitations, marketing campaigns) is sent
inside the HTTP request.
:::

## Filesystem

```dotenv
FILESYSTEM_DISK=local
```

Uploads are stored on the **`public`** disk by the upload actions, so keep
`php artisan storage:link` in your deploy script. An `s3` disk is defined in
`config/filesystems.php` if you want to move later — you will need to change the disk
used by the upload actions.

## Mail

```dotenv
MAIL_MAILER=log          # local: mail is written to storage/logs/laravel.log
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

For Resend:

```dotenv
MAIL_MAILER=resend
RESEND_API_KEY=re_...
RESEND_SEGMENT_ID=...    # optional: attach new marketing contacts to a segment
```

See [Mail and marketing](/docs/operations/mail-and-marketing) for the full flow.

## AI

`config/ai.php` defines the provider list and the defaults:

```php
'default' => 'deepseek',
'default_for_images' => 'gemini',
```

Set the key for the provider you want:

```dotenv
DEEPSEEK_API_KEY=
OPENAI_API_KEY=
ANTHROPIC_API_KEY=
GEMINI_API_KEY=
```

Without a key the **AI Assistant** page and `POST /api/assistant` return an error; the
rest of the application is unaffected. The assistant is registered in
`app/Ai/Agents/CrmAssistant.php`.

## Permissions and broadcasting

`BROADCAST_CONNECTION=log` is the default and means no broadcasting is set up. There are
no broadcast events in the application today.

## Configuration cache

In production, cache the configuration after every deploy:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Clear them during debugging with `php artisan optimize:clear`.

## Next step

[Seed roles and demo data →](/docs/getting-started/seeding-and-demo-data)
