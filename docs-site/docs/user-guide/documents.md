---
title: Documents
sidebar_position: 12
description: Store blueprints, permits, contracts and specifications against a project.
---

# Documents

Documents keep project files together — drawings, permits, signed contracts and
specifications. Requires `manage_documents`.

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| Project | Yes | |
| Name | Yes | Human label, e.g. "Structural drawings — Rev C" |
| Category | Yes | `blueprint`, `cad`, `permit`, `contract`, `spec` |
| File | Yes | Maximum 10 MB |

## Categories

| Category | Contents |
| :--- | :--- |
| `blueprint` | PDF drawings, scanned plans |
| `cad` | DWG/DXF and other CAD formats |
| `permit` | Building permits, approvals, inspections |
| `contract` | Signed contracts, variations, letters of award |
| `spec` | Technical specifications, submittals |

## Storage

Files are written to the `documents` folder on the default filesystem disk (see
[Site photos](/docs/user-guide/site-photos#where-files-live) for the mechanics). The 10 MB
limit is enforced by validation before the file is stored.

## Good practice

- Use a consistent naming convention in **Name**: `<type> — <revision> — <date>`.
- Upload the signed contract here as well as recording it in
  [Contracts](/docs/user-guide/contracts); the record drives financials, the PDF is evidence.
- Category is not enforced anywhere — it is for humans. Be consistent anyway.

## Filtering

Selecting a project in the top bar filters documents to that project.
