---
title: AI agent and tools
sidebar_position: 12
description: How CrmAssistant is built, how tools reach your data, and how to add one.
---

# AI agent and tools

## The agent

`app/Ai/Agents/CrmAssistant.php` is a single agent implementing `Agent`, `Conversational`
and `HasTools`. It is constructed with the acting `User`:

```php
final class CrmAssistant implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    public function __construct(public readonly User $user) {}
}
```

Its `instructions()` method injects company name, currency, timezone, the user's roles,
today's date and the company id, and forbids cross-tenant access. Conversations are stored
in `agent_conversations` / `agent_conversation_messages`.

`tools()` returns ~140 tool instances, each constructed with the same `$user`.

## Tool anatomy

Every tool implements `Tool` with three methods:

```php
final class CreateLead implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    public function description(): Stringable|string
    {
        return 'Create a sales lead with contact details and an initial status.';
    }

    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([...]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', Lead::class);

            $lead = app(CreateLeadAction::class)->execute(new LeadData(...), $this->companyId());

            return ['message' => 'Lead created.', 'lead' => $this->leadSummary($lead)];
        });
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'first_name' => $schema->string()->description('Lead first name.')->required(),
            'status' => $schema->string()->enum(array_column(LeadStatus::cases(), 'value')),
        ];
    }
}
```

## The shared concern

`App\Ai\Tools\Concerns\InteractsWithCompanyData` provides the guard rails:

| Helper | Purpose |
| :--- | :--- |
| `companyId()`, `company()`, `currency()` | Tenant context |
| `toCents()`, `toDecimal()` | Major ↔ minor unit conversion (the model speaks major units) |
| `parseDate()`, `limit()` | Input normalisation |
| `findRecord(Model, id, label, lookups)` | Resolve by UUID, integer id or natural key **within the company** |
| `findTeamMember()`, `findMarketingCampaign()` | Specialised lookups |
| `authorize()`, `authorizePermission()` | Policy/permission checks that become readable errors |
| `attempt()`, `ok()`, `fail()` | Encode `{"ok":true,"data":…}` / `{"ok":false,"error":…}` |

`FormatsCompanyRecords` provides the summary arrays returned to the model.

## Approval-gated tools

Tools that are destructive or grant access implement `Approvable` and use
`InteractsWithApprovals`:

```php
final class DeleteLead implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Deleting a lead removes it from the sales pipeline.');
    }
}
```

The current approval-gated set: all deletes, `DecideChangeOrder`,
`RecordBillingPayment`, `InviteStaffUser`, `InviteClientPortalUser`, `UpdateTeamMemberRole`.

When approval is pending, the agent response carries `pendingApprovals`, the UI shows
approve/reject controls, and the next request resumes the conversation with
`Decisions::from($decisions)`.

## Entry points

| Surface | Code |
| :--- | :--- |
| Web chat | `resources/views/livewire/assistant/chat.blade.php` (streaming, history, approvals) |
| Drawer / quick ask | `assistant/drawer.blade.php`, `assistant/quick-ask.blade.php` |
| API | `POST /api/assistant` → `AssistantController` |

```json
{ "conversation_id": "…", "message": "…", "pending_approvals": [], "usage": {} }
```

## Adding a tool

1. Create `app/Ai/Tools/<Group>/<VerbNoun>.php` implementing `Tool`.
2. Use `InteractsWithCompanyData`; call the **action**, never the model directly.
3. Authorise before doing anything.
4. Describe the tool as if the caller has never seen the schema — the description is the
   model's only hint about *when* to use it.
5. Register it in `CrmAssistant::tools()` under the right comment group.
6. Add a feature test under `tests/Feature/Ai` asserting both success and a permission
   failure.

```php
// CrmAssistant::tools()
new ListLeads($user),
new GetLead($user),
new CreateLead($user),   // <- your new tool
```

## When the assistant must ask

- Ambiguous record → `ToolFailure('Multiple projects match …')`, not a guess.
- Missing permission → `authorizePermission('manage_invoices')` produces
  `You do not have the [manage_invoices] permission required for this action.`
- Destructive → `Approval::required('…')` with a plain-language reason.

## Costs and limits

The assistant is rate limited by the `ai` limiter (**20 requests/minute per user**). Each
turn can call several tools, so token usage is returned in the API `usage` object for
monitoring.
