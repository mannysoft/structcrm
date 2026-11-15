---
title: Site photos
sidebar_position: 11
description: Capture and categorise progress, inspection and safety photos.
---

# Site photos

Photo records attach an image to a project with a category and caption. Requires
`manage_photos` (engineers and foremen have it).

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| Project | Yes | |
| Category | Yes | `before`, `during`, `after`, `inspection`, `safety` |
| Photo | Yes | Image, maximum 5 MB |
| Caption | No | What the photo shows and where |

The uploader and upload time are recorded automatically, and the list shows who uploaded
each photo.

## Categories

| Category | Use it for |
| :--- | :--- |
| `before` | Pre-existing conditions, baseline evidence |
| `during` | Progress records |
| `after` | Completed work, handover evidence |
| `inspection` | Third-party or internal inspections |
| `safety` | Hazards, incidents, corrective actions |

## Where files live

Uploads are written to the `project-photos` folder on the default filesystem disk. The
default disk is `local` (`storage/app/private`), which Laravel serves at `/storage/...`
thanks to `'serve' => true` in `config/filesystems.php`. Files are unlisted in the UI — the
list shows category, project, caption and upload date rather than thumbnails.

:::note
If you switch `FILESYSTEM_DISK` to `s3`, confirm your bucket permissions before uploading
site evidence — losing photos mid-project is a records problem, not just a UI one.
:::

## Filtering

Selecting a project in the top bar filters photos to that project. Search matches captions
and project titles.
