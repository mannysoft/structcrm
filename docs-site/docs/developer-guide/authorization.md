---
title: Authorization
sidebar_position: 7
description: Policies, permissions and the two-layer check that keeps tenants and roles apart.
---

# Authorization

Authorisation has two independent dimensions:

1. **Tenant** — does this record belong to the user's company?
2. **Permission** — does the user's role allow this action?

A request must satisfy both. Policies are where they meet.

## Permissions

Permissions are `manage_*` strings, seeded by `RolePermissionSeeder`. `User` gets them via
`spatie/laravel-permission` (`HasRoles`).

```php
if ($user->hasPermissionTo('manage_invoices')) { /* … */ }
$user->can('manage_invoices');                    // same check
```

The full list lives in `RolePermissionSeeder::PERMISSIONS`. See
[Roles and permissions](/docs/user-guide/roles-and-permissions) for the role matrix.

## Policies

Every domain model has a policy in `Domains/<Domain>/Policies`:

```php
class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->company_id === $lead->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_leads');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->company_id === $lead->company_id
            && $user->hasPermissionTo('manage_leads');
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->company_id === $lead->company_id
            && $user->hasPermissionTo('manage_leads');
    }
}
```

Laravel discovers policies by convention (`App\Domains\Sales\Models\Lead` →
`App\Domains\Sales\Policies\LeadPolicy`). If you add a model in a new namespace, verify
discovery with `php artisan about` or an explicit `Gate::policy()` registration in
`AppServiceProvider`.

## Where checks happen

| Surface | Mechanism |
| :--- | :--- |
| Web forms | Form request `authorize()` → policy |
| Livewire actions | `$this->authorize('delete', $model)` before calling an action |
| API controllers | `$this->authorize('view', $lead)` on show/update/delete |
| API list routes | Controller scopes by `company_id` (no policy call needed for `index`) |
| AI tools | `Gate::forUser($this->user)->authorize(...)` plus `authorizePermission()` |
| Navigation | Team and Marketing entries are hidden without the permission |

The base controller includes `AuthorizesRequests`:

```php
abstract class Controller
{
    use AuthorizesRequests;
}
```

## Livewire pattern

```php
public function delete(DeleteLeadAction $action, string $uuid): void
{
    $lead = Lead::where('uuid', $uuid)->where('company_id', auth()->user()->company_id)->first();

    abort_unless($lead, 404);

    $this->authorize('delete', $lead);

    $action->execute($lead);
}
```

Resolve the record **within the company first**, then authorise. A 404 for another tenant's
record is better than a 403 — it reveals nothing.

## Team management rules

Role changes have extra rules enforced in the component, not the policy:

- You cannot change your own role.
- Only owners can assign or change the `owner` role.
- Non-owners never see `owner` as an option.

If you add a role, update `team/index.blade.php` (`ALL_ROLES`) as well as the seeder.

## Gating UI elements

```blade
@if (auth()->user()->can('manage_users'))
    <x-nav-link :href="route('team.index')">Team</x-nav-link>
@endif
```

Buttons that would fail authorisation are better hidden than shown-and-rejected, but the
server check is what matters — the UI is not a security boundary.

## Testing authorisation

```php
it('forbids a foreman from creating an invoice', function () {
    [$company] = createStaffUser();
    $foreman = User::factory()->role('foreman')->create(['company_id' => $company->id]);

    $this->actingAs($foreman)
        ->post(route('api.invoices.store'), [/* … */])
        ->assertForbidden();
});
```

Always test the cross-tenant case too — see
[Multi-tenancy → Testing tenancy](/docs/developer-guide/multi-tenancy#testing-tenancy).
