---
title: Progress reports
sidebar_position: 20
description: Record accomplished quantities per BOQ line before you bill them.
---

# Progress reports

A progress report is the measurement of work done in a period: which BOQ lines advanced,
by how much, and what that is worth. It is the evidence behind a
[progress billing](/docs/user-guide/progress-billings).

Requires `manage_progress_reports`.

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| Project | Yes | |
| Report date | Yes | Measurement date |
| Status | Yes | `draft`, `submitted`, `approved` |
| Notes | No | Basis of measurement, exclusions |
| Lines | Yes (at least one) | See below |

Each line holds:

| Line field | Required | Notes |
| :--- | :--- | :--- |
| BOQ item | No | Link the line to the BOQ it measures |
| Cost code | No | For grouping |
| Description | Yes | |
| Quantity | Yes | This period's quantity |
| % complete | No | 0–100 |
| Accomplished amount | Yes | Value of the work measured |

The report number is generated as `REP-<seq>`.

## Statuses

| Status | Meaning |
| :--- | :--- |
| `draft` | Being prepared |
| `submitted` | Issued for review |
| `approved` | Accepted — safe to bill |

Status is informational: nothing blocks billing a draft report.

## Monthly rhythm

1. **Measure** on site against the BOQ (quantity and % complete).
2. **Value** the work (`accomplished amount`) using BOQ rates.
3. Set status to `submitted`, review with the client's representative.
4. Move to `approved`.
5. Create the [progress billing](/docs/user-guide/progress-billings) and link this report.

```text
BOQ → Progress report (this period) → Progress billing (gross, retention, net) → Payments
```

## Cautions

- The report does not auto-fill from the BOQ; you enter quantities yourself.
- **% complete and accomplished amount are independent** — nothing validates that they
  agree. Keep them consistent manually.
- Report totals are not stored; they are the sum of the lines.
