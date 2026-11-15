---
title: Examples
sidebar_position: 4
description: End-to-end curl walkthroughs for the most common integrations.
---

# Examples

Set up once:

```bash
export API=http://localhost:8000/api
export TOKEN=$(curl -s -X POST "$API/login" \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{"email":"owner@example.com","password":"password","device_name":"cli"}' \
  | jq -r .token)

curl -s "$API/projects" -H "Authorization: Bearer $TOKEN" -H 'Accept: application/json' | jq
```

## List projects

```bash
curl -s "$API/projects" \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Accept: application/json' | jq '.[0]'
```

```json
{
  "id": "0193f0e2-7c4a-4f1b-9a0e-2f6b7c8d9e01",
  "project_number": "PRJ-2026-001",
  "title": "Meridian Tower",
  "description": null,
  "status": "active",
  "budget": 12500000,
  "currency": "PHP",
  "client_id": 4,
  "manager_id": 2,
  "start_date": "2026-01-15",
  "end_date": "2026-12-20",
  "created_at": "2026-01-10T08:00:00+00:00"
}
```

Remember: `id` is the UUID for URLs, while `client_id` / `manager_id` are integer keys.

## Create a client

```bash
curl -s -X POST "$API/clients" \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{"name":"Meridian Holdings","company_name":"Meridian Holdings Inc.","email":"pm@meridian.test"}' \
  | jq
```

```json
{
  "id": "0193f0e5-1a2b-4c3d-8e4f-5a6b7c8d9e02",
  "name": "Meridian Holdings",
  "email": "pm@meridian.test"
}
```

:::warning[Known gap: clients expose only a UUID]
`StoreProjectRequest` expects `client_id` as the **integer** primary key, but
`ClientResource` only returns the UUID. To create a project through the API you must know
the integer id — read it from an existing project's `client_id`, from the database, or use
the web UI / AI assistant to create the project.
:::

## Create a project for an existing client

```bash
# Discover an integer client id from a project you already have access to
CLIENT_ID=$(curl -s "$API/projects" \
  -H "Authorization: Bearer $TOKEN" -H 'Accept: application/json' | jq -r '.[0].client_id')

curl -s -X POST "$API/projects" \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d "{
    \"title\": \"Meridian Tower Phase 2\",
    \"client_id\": $CLIENT_ID,
    \"status\": \"pending\",
    \"budget\": 4200000,
    \"project_number\": \"PRJ-2026-042\"
  }" | jq '{id, project_number, status}'
```

## Create an invoice with lines

```bash
curl -s -X POST "$API/invoices" \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{
    "project_id": 12,
    "status": "draft",
    "issue_date": "2026-04-01",
    "due_date": "2026-04-30",
    "items": [
      { "description": "Mobilisation", "quantity": 1, "unit_price": 250000 },
      { "description": "Site establishment", "quantity": 2, "unit_price": 18750.5 }
    ]
  }' | jq '{id, invoice_number, total, status}'
```

`total` comes back as `287501` (major units) and the number is assigned by an observer.

## Approve a change order

There is no dedicated "approve" endpoint; update the status:

```bash
curl -s -X PUT "$API/change-orders/$CO_UUID" \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{
    "project_id": 12,
    "description": "Additional retaining wall",
    "amount": 480000,
    "status": "approved"
  }' | jq '{id, co_number, status, amount}'
```

:::caution
`DecideChangeOrderAction` — the code path that also creates a revised budget — is invoked
from the web UI, the portal and the AI assistant. The plain API update changes the status
but **does not** create the revised budget. If budget impact matters, use the assistant
endpoint or the UI.
:::

## Record a progress billing payment

```bash
curl -s -X POST "$API/billings/$BILLING_UUID/payments" \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{
    "payment_date": "2026-04-05",
    "amount": 125000,
    "method": "bank_transfer",
    "reference": "BPI-88213"
  }' | jq '{billing_number, net_amount, paid_amount, balance_amount}'
```

## Create a lead

```bash
curl -s -X POST "$API/leads" \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{
    "first_name": "Ana",
    "last_name": "Reyes",
    "status": "new",
    "email": "ana@example.test",
    "notes": "Referred by Meridian"
  }' | jq
```

## Upload a site photo

Multipart, because the payload contains a file:

```bash
curl -s -X POST "$API/project-photos" \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Accept: application/json' \
  -F 'project_id=12' \
  -F 'category=during' \
  -F 'caption=Level 3 slab pour' \
  -F 'photo=@/path/to/photo.jpg'
```

## AI assistant with approval

```bash
# 1. Ask for something destructive
RESPONSE=$(curl -s -X POST "$API/assistant" \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{"prompt":"Delete the lead ana@example.test"}')

echo "$RESPONSE" | jq '{conversation_id, message, pending_approvals}'

# 2. Approve it, resuming the same conversation
curl -s -X POST "$API/assistant" \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d "{
    \"conversation_id\": \"$(echo "$RESPONSE" | jq -r .conversation_id)\",
    \"decisions\": { \"$(echo "$RESPONSE" | jq -r '.pending_approvals[0].id')\": true }
  }" | jq '{message, pending_approvals}'
```
