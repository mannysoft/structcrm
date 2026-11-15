---
title: Errors and rate limits
sidebar_position: 6
description: Status codes, validation errors, throttling and the usual integration mistakes.
---

# Errors and rate limits

## Status codes

| Code | Meaning | Typical cause |
| :--- | :--- | :--- |
| `200` | OK | Successful read or update |
| `201` | Created | Successful create, and login |
| `204` | No content | Logout, delete |
| `401` | Unauthenticated | Missing/invalid/revoked token |
| `403` | Forbidden | Role lacks the `manage_*` permission, or a form request authorised against another tenant |
| `404` | Not found | Wrong UUID, or the record belongs to another company |
| `405` | Method not allowed | `PATCH` — only `PUT` is registered |
| `419` | CSRF token mismatch | You hit a web route instead of an API route |
| `422` | Unprocessable | Validation failed |
| `429` | Too many requests | Rate limit exceeded |
| `500` | Server error | Check `storage/logs/laravel.log` |

## Validation errors

```json
{
  "message": "The project id field is required. (and 1 more error)",
  "errors": {
    "project_id": ["The project id field is required."],
    "due_date": ["The due date field must be a date after or equal to issue date."]
  }
}
```

Two things to watch:

- **`Rule::enum()` failures** produce a generic message. Check the enum values in
  [Endpoints](/docs/api/endpoints#enumerations).
- **Tenant-scoped `exists` rules** fail with "The selected project id is invalid." when the
  id belongs to another company — which is the intended behaviour.

## Rate limits

| Limiter | Limit | Applies to |
| :--- | :--- | :--- |
| `throttle:6,1` | 6 requests/minute per IP | `POST /api/login` |
| `api` | 60 requests/minute per user (or IP) | Every authenticated route |
| `ai` | 20 requests/minute per user | `POST /api/assistant` |

Exceeding a limit returns `429` with `Retry-After` and `X-RateLimit-*` headers.

```http
HTTP/1.1 429 Too Many Requests
Retry-After: 42
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 0
```

The limiters are defined in `AppServiceProvider::boot()`.

## Common integration mistakes

| Symptom | Cause | Fix |
| :--- | :--- | :--- |
| `401` on every call | Token missing `Bearer ` prefix, or sent as a query param | Send `Authorization: Bearer <token>` |
| HTML login page instead of JSON | No `Accept: application/json` | Add the header |
| `404` for a record you can see in the UI | You used the integer id | Use the UUID from the `id` field |
| `422` on a foreign key | You sent the parent's UUID | Foreign keys are integer ids |
| Amounts 100× too small in your system | You treated the decimal response as cents | Responses are major units |
| `403` for an owner | Token belongs to a different user, or the record is another tenant's | Re-check the token's user |
| Change order approved but budget unchanged | Approved through `PUT` instead of the action | Use the UI or the assistant |
| Photo upload rejected | Sent JSON, or file > 5 MB | Use multipart and check the size |

## Debugging

```bash
# Confirm the token's user
curl -s "$API/projects" -H "Authorization: Bearer $TOKEN" -H 'Accept: application/json' -o /dev/null -w '%{http_code}\n'

# Inspect the route table
php artisan route:list --path=api

# Tail application logs
php artisan pail
```

## Error reporting

Validation and authorisation failures are not logged. Exceptions are logged to
`storage/logs/laravel.log` with `APP_DEBUG=true` exposing details — turn `APP_DEBUG` off in
production.
