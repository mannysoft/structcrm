---
title: Multi-tenancy
sidebar_position: 4
description: How company scoping works, where it does not apply, and the habits that keep data safe.
---

# Multi-tenancy

StructCRM uses **single-database, shared-schema** multi-tenancy: every domain table has a
`company_id`, and every query must be scoped to the authenticated user's company.

## The pieces

```php
// app/Common/Traits/BelongsToTenant.php
protected static function bootBelongsToTenant(): void
{
    static::addGlobalScope(new TenantScope);

    static::creating(function ($model) {
        if (auth()->check() && ! $model->company_id) {
            $model->company_id = auth()->user()->company_id;
        }
    });
}
```

```php
// app/Common/Scopes/TenantScope.php
public function apply(Builder $builder, Model $model): void
{
    if (auth()->check()) {
        $builder->where($model->getTable().'.company_id', auth()->user()->company_id);
    }
}
```

Every domain model uses the trait:

```php
use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

protected $fillable = ['company_id', ...];
```

## The trap: the scope only applies to the `web` guard

`auth()->check()` resolves the **default** guard. That means:

| Context | `auth()->check()` | Tenant scope applied? |
| :--- | :--- | :--- |
| Staff web request | `true` | Yes |
| Sanctum token request (`/api/*`) | usually `false` | **No** |
| Client portal request (`auth:client`) | **No** | **No** |
| Console command / queue job | `false` | **No** |
| Unit test acting as a user | depends on the guard used | Check your test |

Consequences you must respect:

- **API controllers scope explicitly** — `Lead::where('company_id', $request->user()->company_id)`.
- **Portal pages filter by `client_id`** and rely on the `client` guard.
- **Commands and jobs must pass explicit ids** (`$user->id`, `$companyId`) rather than
  relying on ambient auth.
- **Never assume the scope protects you.** Treat it as a safety net for staff UI queries.

## Defence in depth

| Layer | Protects against |
| :--- | :--- |
| `TenantScope` | Accidental cross-tenant reads in staff UI code |
| `creating` hook | Forgetting to set `company_id` |
| Policies (`view`/`update`/`delete`) | Cross-tenant access by id, in web and API |
| `Rule::exists(...)->where('company_id', $companyId)` | Referencing another tenant's records through foreign keys |
| Portal queries filtered by `client_id` | Clients seeing other clients' data |

## Writing a safe query

```php
// Staff UI: scope is applied automatically, but be explicit anyway
$invoices = Invoice::query()
    ->where('company_id', auth()->user()->company_id)
    ->latest()
    ->paginate(15);

// API
$leads = Lead::where('company_id', $request->user()->company_id)->latest()->get();

// Portal
$projects = Project::where('client_id', auth('client')->user()->client_id)->get();

// Job / command
public function handle(): void
{
    $company = Company::find($this->companyId);   // explicit, never ambient
}
```

## Validating cross-tenant references

Every form request that accepts an id validates it against the current company:

```php
'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
```

Do not replace this with `exists:projects,id` — that would accept another tenant's project.

## Testing tenancy

A useful pattern (used across the suite):

```php
$companyA = Company::factory()->create();
$userA = User::factory()->create(['company_id' => $companyA->id]);
$invoiceB = Invoice::factory()->create(['company_id' => Company::factory()->create()->id]);

$this->actingAs($userA)
    ->get(route('invoices.edit', $invoiceB))
    ->assertNotFound();
```

Always test the negative case: another tenant cannot read, update or delete your record.
