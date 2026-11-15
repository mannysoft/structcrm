---
title: Validation and DTOs
sidebar_position: 6
description: How input is validated, authorised and mapped into immutable argument objects.
---

# Validation and DTOs

Every write goes through the same three steps: **authorise → validate → map to a DTO**.

## Form requests

```php
final class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Lead::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(LeadStatus::class)],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function toDTO(): LeadData
    {
        return new LeadData(
            firstName: $this->validated('first_name'),
            lastName: $this->validated('last_name'),
            status: LeadStatus::from($this->validated('status')),
        );
    }
}
```

Rules of thumb:

- **Own the authorisation check here.** The action does not re-check permissions.
- **Validate tenant-owned ids** with `Rule::exists('projects', 'id')->where('company_id', $companyId)`.
- **Use `Rule::enum()`** for every status field; never `in:` lists.
- **`toDTO()` reads `validated()`**, not `input()` — validated data only.

:::warning Kebab-case resources
For kebab-case API resources (`cost-codes`, `boq-items`, `progress-reports`, `change-orders`,
`daily-logs`, `purchase-orders`) the route parameter is **snake_case**. In a form request use
`$this->route('cost_code')`; `$this->route('costCode')` returns `null` and silently
authorises nothing. Controllers can keep camelCase method arguments — implicit binding
normalises them.
:::

## DTOs

DTOs are `final readonly` classes with named constructor arguments and no behaviour:

```php
final readonly class LeadData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public LeadStatus $status,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $notes = null,
    ) {}
}
```

Why bother?

- The action's signature documents exactly what it needs.
- No array-shape guessing, no `$request` leaking into the domain.
- Readonly means an action cannot quietly mutate its input.
- Tests construct DTOs without HTTP.

## Validation in Livewire components

Full-page Livewire components validate inline and then build the DTO themselves:

```php
public function save(CreateLeadAction $action): void
{
    $this->validate([
        'first_name' => ['required', 'string', 'max:255'],
        // …
    ]);

    $action->execute(new LeadData(...), auth()->user()->company_id);

    session()->flash('status', 'Lead created.');
}
```

Keep the validation rules in the component consistent with the form request — duplicated
rules are a known maintenance cost. If you add a field, update both.

## Validation conventions

| Input | Rule |
| :--- | :--- |
| Status/enums | `Rule::enum(EnumClass::class)` |
| Money | `['required', 'numeric', 'min:0']`, converted to cents in the DTO |
| Dates | `['nullable', 'date']`; end dates use `after_or_equal:start` |
| Files | `['required', 'file', 'max:10240']` (10 MB) or `image`, `max:5120` for photos |
| Percentages | `['numeric', 'min:0', 'max:100']` |
| Tenant ids | `Rule::exists(table, 'id')->where('company_id', $companyId)` |
| Line items | `['required', 'array', 'min:1']` plus nested `items.*.*` rules |

## Error messages

Validation failures surface inline under each field and the form keeps its state. There is
no custom message catalogue — Laravel's default messages are used.
