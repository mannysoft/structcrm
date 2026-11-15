---
title: Daily logs
sidebar_position: 10
description: Record what happened on site each day — weather, crew, hours, issues and safety.
---

# Daily logs

Daily logs are the field diary. One entry per project per day is normal, though nothing
prevents duplicates. Requires `manage_daily_logs` (foremen and engineers have it).

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| Project | Yes | |
| Log date | Yes | Defaults to today on the create form |
| Work performed | Yes | Narrative of what was done |
| Weather | No | Free text, e.g. "Heavy rain, 24°C" |
| Crew count | No | Number of workers on site |
| Labour hours | No | Total hours worked |
| Equipment used | No | Free text |
| Issues | No | Delays, access problems, RFIs |
| Safety incidents | No | Near misses, incidents, toolbox talks |

## Writing a useful log

1. Log **the same day**, not a week later.
2. Be specific about quantities and locations ("poured slab zone B, 18 m³").
3. Record delays with their cause — this is your evidence in a claim.
4. Note the crew number: it is the fastest way to sanity-check labour cost later.
5. Record safety incidents even when nothing was hurt.

## Filtering

Selecting a project in the top bar filters the list to that project. Search matches the
work performed text and project title.

## What daily logs do **not** do

- They do not create time entries — record crew hours in
  [time tracking](/docs/user-guide/time-tracking) if you need per-person hours.
- They do not post labour cost — create an [expense](/docs/user-guide/expenses) for that.
