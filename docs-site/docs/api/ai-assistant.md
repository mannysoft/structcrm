---
title: AI assistant endpoint
sidebar_position: 5
description: Drive the StructCRM assistant from your own tooling, including the approval handshake.
---

# AI assistant endpoint

```http
POST /api/assistant
Authorization: Bearer <token>
Content-Type: application/json
```

Staff users only. Client portal users receive `403`.

## Start or continue a conversation

```json
{
  "prompt": "Which projects have the worst margin this quarter?",
  "conversation_id": null
}
```

`conversation_id` is optional. Omit it for a new conversation; the response returns the id to
continue with.

```json
{
  "conversation_id": "9f2c4b1e-8f0a-4b3c-9d21-6c8c1b2f4e77",
  "message": "Three projects are below your 12% target margin…",
  "pending_approvals": [],
  "usage": { "input_tokens": 1840, "output_tokens": 260 }
}
```

## Approvals

When the assistant wants to run a tool that needs human approval, it **stops** and returns
the pending actions:

```json
{
  "conversation_id": "9f2c…",
  "message": "I can delete invoice INV-12. Approve?",
  "pending_approvals": [
    {
      "id": "appr_01H…",
      "tool": "DeleteInvoice",
      "arguments": { "invoice": "INV-12" },
      "reason": "Deleting an invoice removes a financial record."
    }
  ],
  "usage": {}
}
```

Resume by sending decisions for that conversation:

```json
{
  "conversation_id": "9f2c4b1e-…",
  "decisions": { "appr_01H…": true }
}
```

`decisions` maps approval id → boolean. `true` approves, `false` rejects and the assistant
reports the rejection instead of running the tool.

| Rule | Detail |
| :--- | :--- |
| `prompt` vs `decisions` | One of them is required; `conversation_id` is required with `decisions` |
| Ownership | The conversation must belong to the authenticated user (`404` otherwise) |
| Partial decisions | Any approval without a decision is treated as rejected |
| Prompt length | Maximum 4000 characters |

## Approval-gated tools

All deletes, plus `DecideChangeOrder`, `RecordBillingPayment`, `InviteStaffUser`,
`InviteClientPortalUser` and `UpdateTeamMemberRole`.

## What the assistant can reach

The same tools as the web chat: analytics, company and team, projects and field records,
finance, sales and marketing. Every tool is tenant-scoped and permission-checked, so the
endpoint cannot do more than the token's user can do in the UI.

## Rate limits and cost

- **20 requests per minute per user** (`ai` limiter).
- Each turn can trigger several tool calls; `usage` reports token counts for cost tracking.
- The agent is configured in `config/ai.php`; the default provider is `deepseek`.

## Errors

| Status | Meaning |
| :--- | :--- |
| `403` | Client portal user, or no AI provider configured |
| `404` | `conversation_id` does not belong to this user |
| `422` | Missing `prompt`, missing `conversation_id` with `decisions`, or prompt too long |
| `429` | More than 20 requests/minute |

## Related

- [AI assistant (user guide)](/docs/user-guide/ai-assistant)
- [AI agent and tools (developer guide)](/docs/developer-guide/ai-agent-and-tools)
