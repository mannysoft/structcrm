---
title: Fleet and maintenance
sidebar_position: 14
description: Keep an equipment register and a maintenance service history.
---

# Fleet and maintenance

Two linked modules: **Fleet & Equipment** (what you own) and **Maintenance Logs** (what you
did to it). Equipment requires `manage_equipment`; maintenance logs require
`manage_maintenance_logs`.

## Equipment

| Field | Required | Notes |
| :--- | :--- | :--- |
| Name | Yes | e.g. "Backhoe #2" |
| Type | Yes | `heavy_machinery`, `tool`, `vehicle`, `other` |
| Status | Yes | `available`, `in_use`, `maintenance`, `retired` |
| Service due | No | Next scheduled service date |
| Notes | No | Serial number, ownership, operator notes |

Equipment is **company-wide**, not project-specific: a backhoe moves between sites.

## Maintenance logs

| Field | Required | Notes |
| :--- | :--- | :--- |
| Equipment | Yes | Must belong to your company |
| Service date | Yes | |
| Description | Yes | What was done |
| Cost | No | Stored in minor units with a currency |
| Currency | No | 3-letter code, defaults to company currency |

## Keeping it useful

1. Set **Service due** when equipment comes back from service; that date is the reminder
   (there is no automatic notification yet).
2. Log every service with a cost so equipment spend is visible per asset.
3. Move status to `maintenance` while it is off site — the fleet list is the only place
   this is visible, so keep it honest.
4. Move retired assets to `retired` instead of deleting them; maintenance history stays
   readable.

## Cost accounting caveat

Maintenance cost lives on the maintenance log, **not** in
[expenses](/docs/user-guide/expenses). If you want it in project margins, also record an
expense with category `equipment`.
