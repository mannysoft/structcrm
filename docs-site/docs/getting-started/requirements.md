---
title: Requirements
sidebar_position: 1
description: Server and tooling requirements for running StructCRM locally or in production.
---

# Requirements

## Runtime

| Requirement | Version | Notes |
| :--- | :--- | :--- |
| PHP | **8.4+** | The domain models use PHP 8.4 property hooks for money hydration, so 8.3 will fail even though `composer.json` allows it. |
| Composer | 2.x | |
| Node.js | 20+ | Required to build assets with Vite. |
| npm | 10+ | |

### PHP extensions

Enable these extensions before installing dependencies:

```bash
php -m | grep -E 'mbstring|dom|fileinfo|sqlite3|pdo_sqlite|bcmath|openssl|tokenizer|xml|ctype|json|curl'
```

`mbstring`, `dom`, `fileinfo`, `sqlite3`/`pdo_sqlite` and `bcmath` are all used by the
application or its test suite.

## Database

- **SQLite** is the default and what the test suite uses. Nothing else is required.
- **MySQL 8+** is supported for production. Point `DB_CONNECTION=mysql` and provide the
  usual `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD`. The schema
  uses `utf8mb4`.

Sessions, cache, queues and jobs use the **database** driver out of the box, so the same
connection backs the whole app. Redis is optional.

## Optional services

| Service | Needed for | Configuration |
| :--- | :--- | :--- |
| AI provider | The AI assistant | Any provider key in `config/ai.php` (e.g. `DEEPSEEK_API_KEY`) |
| Resend | Marketing and production email | `MAIL_MAILER=resend`, `RESEND_API_KEY` |
| Chrome | Laravel Dusk browser tests | `php artisan dusk:chrome-driver --detect` |
| SMTP server | Local mail testing | `MAIL_MAILER=smtp` or use `log` |

## Next step

[Install StructCRM →](/docs/getting-started/installation)
