---
title: Authentication
sidebar_position: 1
description: Issue a Sanctum token and authenticate every request.
---

# Authentication

The API uses **Laravel Sanctum personal access tokens**. There are no OAuth flows, no
scopes and no refresh tokens.

## Issue a token

```http
POST /api/login
Content-Type: application/json
```

```json
{
  "email": "owner@example.com",
  "password": "password",
  "device_name": "Reporting script"
}
```

```http
HTTP/1.1 201 Created

{ "token": "1|7Xb2…" }
```

`device_name` is required and becomes the token's label (`personal_access_tokens.name`).
Store the token securely — it is shown once.

:::warning Staff accounts only
The login endpoint filters on `users.type = staff`. Client portal accounts can never obtain
an API token, even with correct credentials.
:::

## Use the token

```http
GET /api/projects
Authorization: Bearer 1|7Xb2…
Accept: application/json
```

Always send `Accept: application/json` so errors come back as JSON rather than a redirect
to the login page.

## Revoke the current token

```http
POST /api/logout
Authorization: Bearer 1|7Xb2…
```

```http
HTTP/1.1 204 No Content
```

This deletes only the token used for the request. Other devices stay signed in.

## Rate limiting on authentication

`POST /api/login` is limited to **6 attempts per minute** per IP (`throttle:6,1`). Exceeding
it returns `429` with a `Retry-After` header.

## What a token can do

A token acts as the user who created it:

- Same permissions as that user's role.
- Same tenant (`company_id`) — you cannot reach another company's data.
- Same policies — a `foreman` token cannot create invoices.

If you need narrower access for an integration, create a dedicated staff user with the
minimum role instead of sharing an owner token.

## Token lifetime

Tokens do not expire unless you set `expires_at` yourself. Rotate them by issuing a new
token and calling `POST /api/logout` on the old one, or delete the row from
`personal_access_tokens`.
