---
title: Leads
sidebar_position: 6
description: Track prospective work from first contact to conversion.
---

# Leads

A lead is a prospective client or job. Leads are deliberately lightweight: enough to track
a pipeline, not a full CRM deal engine.

Requires `manage_leads`.

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| First name | Yes | |
| Last name | Yes | |
| Status | Yes | See pipeline below |
| Email | No | |
| Phone | No | |
| Notes | No | Site visit notes, budget indications, next actions |

## Pipeline

| Status | Meaning |
| :--- | :--- |
| `new` | Just captured, no contact yet |
| `contacted` | First contact made |
| `qualified` | Real budget, real timeline, decision maker known |
| `lost` | Dead — keep it for reporting |
| `converted` | Became a client/project |

The list page shows the status of every lead; filter with search to find a name or email.

## Working a lead

1. Capture the lead the moment it arrives.
2. Move it to `contacted`, then `qualified` as you learn more.
3. Price the work with an [estimate](/docs/user-guide/estimates), linking the estimate to
   the lead.
4. When the job is won, create the [client](/docs/user-guide/clients) and
   [project](/docs/user-guide/projects), then set the lead to `converted`.
5. If it dies, mark `lost` rather than deleting — the history is worth keeping.

:::tip
Estimates can be linked to either a lead (pre-sale) or a project (post-sale). Creating the
estimate first, then converting, keeps the numbers traceable.
:::
