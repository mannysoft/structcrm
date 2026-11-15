---
title: Environment variables
sidebar_position: 1
description: Every environment variable StructCRM reads, grouped by concern.
---

# Environment variables

Copy `.env.example` to `.env` and adjust. Variables below are read by `config/*.php`; the
ones marked **required** have no working default in production.

## Application

| Variable | Default | Notes |
| :--- | :--- | :--- |
| `APP_NAME` | `Laravel` | Shown in mail and the UI; set to `StructCRM` |
| `APP_ENV` | `local` | `production` in production |
| `APP_KEY` | — | **Required.** `php artisan key:generate` |
| `APP_DEBUG` | `true` | Must be `false` in production |
| `APP_URL` | `http://localhost` | **Required for signed links** (invitations, unsubscribe) |
| `APP_TIMEZONE` | `UTC` | Company timezone overrides this per tenant |
| `APP_LOCALE`, `APP_FALLBACK_LOCALE`, `APP_FAKER_LOCALE` | `en`, `en`, `en_US` | |
| `APP_MAINTENANCE_DRIVER` | `file` | |

## Database

| Variable | Default | Notes |
| :--- | :--- | :--- |
| `DB_CONNECTION` | `sqlite` | `sqlite`, `mysql`, `pgsql`, `sqlsrv` |
| `DB_DATABASE` | `database/database.sqlite` | Path for SQLite, name for MySQL |
| `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` | — | MySQL only |
| `DB_URL` | — | Alternative DSN |

## Session, cache and queue

| Variable | Default | Notes |
| :--- | :--- | :--- |
| `SESSION_DRIVER` | `database` | `file`, `redis`, `array` |
| `SESSION_LIFETIME` | `120` | Minutes |
| `SESSION_ENCRYPT` | `false` | |
| `SESSION_PATH`, `SESSION_DOMAIN` | `/`, `null` | |
| `CACHE_STORE` | `database` | `file`, `redis`, `array` |
| `CACHE_PREFIX` | — | |
| `QUEUE_CONNECTION` | `database` | `sync` sends inline, `redis` needs a server |
| `BROADCAST_CONNECTION` | `log` | No broadcast events are used |

## Mail

| Variable | Default | Notes |
| :--- | :--- | :--- |
| `MAIL_MAILER` | `log` | `log`, `smtp`, `resend`, `array` |
| `MAIL_SCHEME`, `MAIL_HOST`, `MAIL_PORT` | — | SMTP |
| `MAIL_USERNAME`, `MAIL_PASSWORD` | — | SMTP |
| `MAIL_FROM_ADDRESS` | `hello@example.com` | **Change in production** |
| `MAIL_FROM_NAME` | `${APP_NAME}` | |

## Marketing / Resend

| Variable | Notes |
| :--- | :--- |
| `RESEND_API_KEY` | Required for `MAIL_MAILER=resend` and Resend contact sync |
| `RESEND_SEGMENT_ID` | Optional segment for new marketing contacts |

## Storage

| Variable | Default | Notes |
| :--- | :--- | :--- |
| `FILESYSTEM_DISK` | `local` | Uploads call `store()` without a disk |
| `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET` | — | Only if you switch to `s3` |
| `AWS_USE_PATH_STYLE_ENDPOINT` | `false` | MinIO/compatible storage |

## AI providers

Set the key(s) for the provider you use. `config/ai.php` defaults to `deepseek`.

| Variable |
| :--- |
| `ANTHROPIC_API_KEY` |
| `AZURE_OPENAI_API_KEY` |
| `COHERE_API_KEY` |
| `DEEPSEEK_API_KEY` |
| `ELEVENLABS_API_KEY` |
| `GEMINI_API_KEY` |
| `GROQ_API_KEY` |
| `MISTRAL_API_KEY` |
| `OLLAMA_API_KEY` |
| `OPENAI_API_KEY` |
| `OPENAI_COMPATIBLE_API_KEY`, `OPENAI_COMPATIBLE_URL` |
| `OPENROUTER_API_KEY` |
| `JINA_API_KEY` |
| `TYPESAFE_API_KEY` |
| `VOYAGEAI_API_KEY` |
| `XAI_API_KEY` |

## Other

| Variable | Default | Notes |
| :--- | :--- | :--- |
| `BCRYPT_ROUNDS` | `12` | Tests use 4 |
| `LOG_CHANNEL`, `LOG_STACK`, `LOG_LEVEL` | `stack`, `single`, `debug` | `error` in production |
| `REDIS_*` | — | Only if you use Redis |
| `VITE_APP_NAME` | `${APP_NAME}` | Build-time |

## Production minimum

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://crm.example.com
APP_KEY=base64:...

DB_CONNECTION=mysql
DB_HOST=...
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

MAIL_MAILER=resend
RESEND_API_KEY=re_...
MAIL_FROM_ADDRESS=no-reply@example.com

QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database
```

Remember to re-run `php artisan config:cache` after changing any of these.
