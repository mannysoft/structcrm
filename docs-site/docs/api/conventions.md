---
title: Conventions
sidebar_position: 2
description: Identifiers, money, dates, payloads and the response shapes the API returns.
---

# Conventions

## Base path

All endpoints live under `/api`. There is **no** `/v1` prefix yet.

```text
http://localhost:8000/api/projects
https://your-host.example.com/api/projects
```

## Identifiers

| Field | Type | Notes |
| :--- | :--- | :--- |
| `id` (resource) | UUID string | Public identifier — use it in URLs |
| `project_id`, `client_id`, `supplier_id`, `manager_id`, `lead_id` | integer | Internal foreign keys, as stored |
| `uuid` on response | — | Not returned separately; `id` **is** the UUID |

Route parameters are resolved by UUID (`getRouteKeyName()` returns `uuid`), so
`/api/projects/{uuid}` works and `/api/projects/{integer}` returns `404`.

:::warning Foreign keys are integer ids, and not every parent exposes one
`project_id`, `client_id`, `supplier_id`, `lead_id` and `manager_id` are validated with
`Rule::exists(table, 'id')`, so they take the **integer** key. Only `ProjectResource`
returns `client_id`; `ClientResource`, `SupplierResource` and `LeadResource` return just
their UUID. Discover a client's integer id from an existing project, or create the record
through the UI / assistant.
:::

Nested resource names with multiple words are kebab-case in the path and snake_case in the
parameter: `/api/cost-codes/{cost_code}`, `/api/progress-reports/{progress_report}`.

## Money

| Rule | Detail |
| :--- | :--- |
| **Responses** | Decimal major units (`1500.25`) |
| **Requests** | Decimal major units too — the controller converts to minor units |
| **Currency** | Returned on resources that carry one (`currency`, `contract_value_currency`, …) |
| **Rounding** | Two decimals; extra precision is rounded |

Stored cents are converted with `/ 100` or `Money::toDecimal()` before serialisation, so a
`total` of `250000` in the database is returned as `2500`.

## Dates

| Kind | Format | Example |
| :--- | :--- | :--- |
| `date` columns | `YYYY-MM-DD` | `2026-03-31` |
| `datetime` columns | ISO-8601 | `2026-03-31T09:15:00+00:00` |
| Request datetimes | Any `strtotime`-parseable string | `2026-03-31 09:15` |

## Enums

Every status is a lowercase snake_case string, validated against a backed enum:

```json
{ "status": "in_progress" }
```

An unknown value returns `422` with a validation message listing nothing useful — check the
documented values in [Endpoints](/docs/api/endpoints).

## Collections

`GET` on a collection returns a **plain array** of resources: no pagination envelope, no
meta, no links. Results are newest first with no `per_page` support.

```json
[
  { "id": "0193f0e2-…", "title": "Meridian Tower" },
  { "id": "0193f0e3-…", "title": "Harbor Fit-out" }
]
```

If you need pagination, filter client-side or use the query parameters your module exposes
(the API currently has none).

## Response shapes

| Operation | Status | Body |
| :--- | :--- | :--- |
| `GET` collection | `200` | Array of resources |
| `GET` single | `200` | Resource object |
| `POST` create | `201` | Created resource |
| `PUT/PATCH` update | `200` | Updated resource |
| `DELETE` | `204` | Empty |
| Validation failure | `422` | `{ "message": "…", "errors": { "field": ["…"] } }` |
| Unauthenticated | `401` | `{ "message": "Unauthenticated." }` |
| Not found (or another tenant) | `404` | `{ "message": "No query results for model …" }` |
| Forbidden (role) | `403` | `{ "message": "This action is unauthorized." }` |
| Throttled | `429` | `{ "message": "Too Many Attempts." }` |

## Updates

`PUT` is the only update verb registered. Send the full resource; form requests validate the
same rules as create, so omitting a required field returns `422`.

Delete is soft for most models: the row keeps `deleted_at` and disappears from responses.

## Content type

Send `Content-Type: application/json` and `Accept: application/json`. File uploads are not
supported through the API today — use the web UI for photos, documents and receipts.
