---
title: Time tracking
sidebar_position: 13
description: Record clock-in and clock-out times, breaks and locations.
---

# Time tracking

Time entries capture attendance and hours per project. Requires `manage_time_entries`
(foremen and staff have it).

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| Project | Yes | |
| Clock in | Yes | Date and time |
| Clock out | No | Must be on or after clock in |
| Break minutes | No | Deducted from the elapsed time |
| Latitude / longitude | No | Optional location captured by the form |
| Notes | No | Task performed, crew split |

## How duration is calculated

```text
duration (minutes) = (clock out − clock in) − break minutes
```

An open entry (no clock out) has no duration. There is no validation that prevents
overlapping entries for the same person — review before exporting.

## Using it well

- Clock out the same day. Open entries are easy to forget and impossible to cost.
- Use **Notes** to distinguish work types if one person splits the day across scopes.
- Time entries are operational data: they do **not** create expenses or feed billing.
  To cost labour, create an [expense](/docs/user-guide/expenses) with category `labor`.

:::note[Geometry has no effect]
Latitude and longitude are stored, but there is no geofencing, no map view and no
enforcement of where a clock-in happens.
:::

## Filtering

Selecting a project in the top bar filters entries to that project.
