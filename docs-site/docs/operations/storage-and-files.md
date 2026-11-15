---
title: Storage and files
sidebar_position: 3
description: Where uploads go, how they are served, and what to back up.
---

# Storage and files

## What gets uploaded

| Upload | Path prefix | Limit |
| :--- | :--- | :--- |
| Site photos | `project-photos/` | 5 MB, image |
| Project documents | `documents/` | 10 MB, any file |
| Expense receipts | `receipts/` | 2 MB, image |
| Company logo | `company-logos/` | 2 MB, image |

Uploads call `store()` **without** an explicit disk, so they land on `FILESYSTEM_DISK`.

## The default disk

`FILESYSTEM_DISK=local` resolves to `storage/app/private` (`'serve' => true`), which Laravel
serves through its own route — no symlink required and no public directory exposure. This is
the recommended default for project evidence: files are not enumerable from the web root.

If you switch to `public`, you must run `php artisan storage:link` once per deployment.

## Moving to object storage

An `s3` disk is configured but unused. To adopt it:

1. Set `FILESYSTEM_DISK=s3` and the `AWS_*` variables.
2. Add the Flysystem S3 adapter if it is not already installed (`composer require
   league/flysystem-aws-s3-v3`).
3. Migrate existing files (`aws s3 sync storage/app/private s3://bucket/`) and keep the same
   path prefixes.
4. Verify `Storage::url()` still resolves in the views you use.

Do not switch mid-project without migrating: existing rows point at paths that will 404.

## Permissions

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache
```

`storage` and `bootstrap/cache` must be writable by the web user. Everything else can be
read-only.

## Sizing and retention

Site photos are the biggest consumer. Two sensible policies:

- **Project lifecycle:** archive a project's photos to cold storage after handover.
- **Retention:** keep inspection and safety photos for your contractual period (often
  2–6 years), delete `during` photos at handover.

The application does not expire files by itself.

## Backups

Back up three things, together:

1. **The database** (daily, plus before every deploy).
2. **The storage disk** (uploads are not reproducible).
3. **`.env`** (recoverable from your secret manager, never from git).

A database backup without the matching files is an incomplete restore: photo and document
rows will point at nothing.
