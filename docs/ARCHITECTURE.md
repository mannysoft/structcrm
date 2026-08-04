# ConstructFlow — Architecture Guide

**Version:** 2.0  
**Stack Target:** Laravel 13 / PHP 8.4+ / Livewire 3 & Volt / Tailwind CSS 4  

---

## 1. System Technology Stack

```
┌─────────────────────────────────────────────────────────────────┐
│                       Frontend Presentation                     │
│    Livewire 3 + Volt  │  Alpine.js  │  Tailwind CSS 4 (Vite)    │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Application & Domain                       │
│    Laravel 13 (PHP 8.4+)  │  Laravel Breeze  │  Pest PHP     │
└─────────────────────────────────────────────────────────────────┘
                                │
                ┌───────────────┴───────────────┐
                ▼                               ▼
┌───────────────────────────────┐   ┌───────────────────────────────┐
│        Data Persistence       │   │   Background & Queue Layer    │
│      MySQL 8.0+ / InnoDB      │   │    Redis 7.0+ & Horizon      │
└───────────────────────────────┘   └───────────────────────────────┘
```

| Component | Technology | Version | Purpose |
| :--- | :--- | :--- | :--- |
| **Language** | PHP | 8.4+ | Core execution runtime with modern property hooks and type safety. |
| **Framework** | Laravel | 13.x | Application engine, routing, ORM, queues, and container. |
| **Reactive UI** | Livewire + Volt | 3.x | Component-driven reactive UI without full SPA frontend overhead. |
| **Client Scripting** | Alpine.js | 3.x | Lightweight DOM behavior, dropdowns, and client-side modal state. |
| **Styling** | Tailwind CSS | 4.x | Utility-first styling compiled via Vite engine. |
| **Database** | MySQL | 8.0+ | Relational data persistence with foreign keys and transactional integrity. |
| **Cache & Queue** | Redis + Horizon | 7.0+ | Fast session caching, lock management, and queue worker dashboard. |
| **File Storage** | Amazon S3 | API v3 | Cloud object storage for site photos, blueprints, and exported PDFs. |
| **Testing** | Pest PHP | 3.x | Expressive, modern test suite for unit, feature, and architecture tests. |

---

## 2. Layered Architecture Pattern

ConstructFlow follows a **Thin Presentation / Action-Oriented Layered Architecture** to keep domain logic clean, testable, and decoupled from the web transport.

```
Request ──► Middleware (Tenant Verification)
                 │
                 ▼
         Livewire / Volt Component (Presentation)
                 │
                 ▼
          Form Request / Validation Rules
                 │
                 ▼
            Action Class (App\Actions\*) ◄── Executed in Transaction
                 │
        ┌────────┴────────┐
        ▼                 ▼
   Eloquent Model     Event Dispatch (App\Events\*)
   (Global Scope)         │
                          ▼
                     Queued Job / Listener (App\Jobs\*)
```

### Layer Breakdown

1. **Presentation Layer (`App\Livewire\...` or Volt Components):**
   * Handles user interaction, form input binding, and view rendering.
   * **Strict Rule:** No complex SQL queries or direct business transactions inside components. Delegates immediately to Action classes.

2. **Validation Layer (`App\Http\Requests\...`):**
   * Encapsulates parameter validation, input sanitization, and basic authorization rules.

3. **Action Layer (`App\Actions\...`):**
   * Single-responsibility PHP classes that perform atomic business transactions (e.g., `CreateProjectAction`, `ApproveChangeOrderAction`, `GenerateInvoiceAction`).
   * Wrapped in database transactions (`DB::transaction`) to ensure atomicity.

4. **Domain Model Layer (`App\Models\...`):**
   * Eloquent models augmented with relationship definitions, accessors, mutators, and custom query builders.
   * Implements `BelongsToTenant` trait for multi-tenant data isolation.

5. **Security & Policy Layer (`App\Policies\...`):**
   * Model-level authorization ensuring users can only read/update resources belonging to their active `company_id`.

6. **Asynchronous Layer (`App\Jobs\...`, `App\Events\...`):**
   * Non-blocking queue processing for PDF rendering, email dispatch, S3 image optimization, and notification broadcasting.

---

## 3. Multi-Tenancy Architecture

ConstructFlow implements a **Single-Database Multi-Tenant Isolation Pattern** for optimal scalability, simplified migrations, and cost-efficient database pooling.

### 3.1 Tenant Isolation Strategy
Every database table containing client or project data includes a `company_id` foreign key column referencing `companies.id`.

```php
namespace App\Traits;

use App\Models\Scopes\TenantScope;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            if (auth()->check() && ! $model->company_id) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }
}
```

### 3.2 Tenant Global Scope
```php
namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check()) {
            $builder->where($model->getTable() . '.company_id', auth()->user()->company_id);
        }
    }
}
```

---

## 4. Directory & Namespace Structure

```
app/
├── Actions/                  # Single-purpose business transaction actions
│   ├── Clients/
│   ├── Projects/
│   ├── Estimates/
│   ├── DailyLogs/
│   └── Invoices/
├── Console/                  # Scheduled artisan commands
├── Events/                   # Domain event triggers
├── Http/
│   ├── Controllers/          # API & Web controllers
│   ├── Middleware/           # IdentifyTenant, VerifySubscription
│   └── Requests/             # Form validation classes
├── Jobs/                     # Queued async jobs (PDFs, S3 resize, Emails)
├── Livewire/                 # Livewire full-page & nested components
├── Models/                   # Eloquent Models
│   └── Scopes/               # TenantScope
├── Notifications/            # Multi-channel notifications
├── Policies/                 # Policy authorization classes
├── Services/                 # Third-party integrations (Stripe, S3, PDF)
└── Traits/                   # BelongsToTenant, HasUuid
```

---

## 5. Development Principles & Code Standards

### Core Guidelines
* **SOLID Principles:** Single Responsibility per Action class, Open-Closed design for modules, Interface segregation for services.
* **PSR-12 Compliance:** Strictly formatted code enforced via `laravel/pint`.
* **Zero Fat Controllers / Components:** Livewire components only collect input and trigger Actions.
* **No Logic in Blade:** Blade templates only present pre-computed view data.
* **Strict Type Declarations:** Every method must have explicit argument types and return type declarations (`declare(strict_types=1);`).

---

## 6. Testing & Quality Assurance Strategy

ConstructFlow utilizes **Pest PHP** for comprehensive automated testing across three tiers:

1. **Unit Tests (`tests/Unit`):** Isolated tests for Action classes, value objects, and utility helpers without database overhead.
2. **Feature Tests (`tests/Feature`):** Full end-to-end component tests verifying Livewire inputs, policy enforcement, database mutations, and queue dispatch.
3. **Tenant Security Tests (`tests/Feature/TenancyTest.php`):** Dedicated assertion suite ensuring User A in Company 1 cannot access, modify, or leak data to User B in Company 2.

```php
test('user cannot view projects belonging to another tenant', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $userA = User::factory()->create(['company_id' => $companyA->id]);
    $projectB = Project::factory()->create(['company_id' => $companyB->id]);

    $this->actingAs($userA);

    expect(Project::find($projectB->id))->toBeNull();
});
```
