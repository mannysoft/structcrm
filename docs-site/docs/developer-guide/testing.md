---
title: Testing
sidebar_position: 14
description: Pest conventions, the helpers in tests/Pest.php, and how to run Dusk.
---

# Testing

The suite uses **Pest 4** on top of PHPUnit: 116 test files across `tests/Feature`,
`tests/Unit` and `tests/Browser`.

## Running tests

```bash
php artisan test --compact                       # everything
php artisan test --compact tests/Feature/LeadPageTest.php
php artisan test --compact --filter=creates_a_lead
php artisan test --compact --testsuite=Feature
```

Do not create PHPUnit-style classes — write Pest functions:

```php
test('owner can create a lead', function () {
    // …
});

it('rejects a foreman creating an invoice', function () {
    // …
});
```

## Global setup

`tests/Pest.php` applies the base test case and database refresh to every test:

```php
uses(TestCase::class, RefreshDatabase::class)->in('Feature', 'Unit');
uses(DuskTestCase::class)->in('Browser');
```

`Tests\TestCase::setUp()` seeds `RolePermissionSeeder`, so permissions and roles exist in
every test without extra work.

`phpunit.xml` pins the environment: in-memory SQLite, `array` cache/session, `sync` queue,
`array` mail, 4 bcrypt rounds.

## Helpers

| Helper | Returns |
| :--- | :--- |
| `createStaffUser()` | `[Company, User]` — owner role, password `password` |
| `makeProject(Company $company)` | A `Project` with its own client |
| `createTempFile()`, `createTempCsv()`, `createTempImage()` | Paths for upload/import tests |

Dusk macros (`waitForLivewire()`, `waitForLivewireToLoad()`, `setInputValue()`,
`addRepeaterItem()`) and `loginAsStaff()` live in the same file.

## Factories

One factory per model, with useful states:

```php
User::factory()->create(['company_id' => $company->id]);      // owner by default
User::factory()->role('foreman')->create([...]);
User::factory()->client()->create(['client_id' => $client->id]);
User::factory()->unverified()->create();
```

Relationships are **not** created automatically for domain models — always pass
`company_id` (and `project_id`, `client_id`) explicitly so tenancy stays obvious:

```php
$project = Project::factory()->create([
    'company_id' => $company->id,
    'client_id' => Client::factory()->create(['company_id' => $company->id])->id,
]);
```

## What a good test covers

1. **Happy path** — the record is created/updated and the response/redirect is right.
2. **Validation** — a required field or bad enum value is rejected.
3. **Authorisation** — a role without the permission gets 403.
4. **Tenancy** — another company's record yields 404.
5. **Side effects** — document number assigned, budget created on change-order approval,
   notification queued.

```php
it('creates a revised budget when a change order is approved', function () {
    [$company, $user] = createStaffUser();
    $project = makeProject($company);
    $changeOrder = ChangeOrder::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'status' => 'pending',
        'amount' => 250000,
    ]);

    app(DecideChangeOrderAction::class)->execute($changeOrder, ChangeOrderStatus::Approved);

    expect($changeOrder->fresh()->status)->toBe(ChangeOrderStatus::Approved)
        ->and(Budget::where('project_id', $project->id)->where('is_current', true)->exists())->toBeTrue();
});
```

## Browser tests (Dusk)

Dusk needs a real server and a dedicated database.

```bash
# 1. Prepare the database
touch database/dusk.sqlite
DB_CONNECTION=sqlite DB_DATABASE=database/dusk.sqlite php artisan migrate:fresh --force
DB_CONNECTION=sqlite DB_DATABASE=database/dusk.sqlite php artisan db:seed --class=RolePermissionSeeder --force

# 2. Start the server against the same database
APP_URL=http://127.0.0.1:8000 DB_CONNECTION=sqlite DB_DATABASE=database/dusk.sqlite \
  php artisan serve --host=127.0.0.1 --port=8000 --no-reload

# 3. Run the tests
php artisan dusk
php artisan dusk --filter=AuthenticationTest
```

Requires Chrome; `php artisan dusk:chrome-driver --detect` installs a matching driver.
`--no-reload` stops the server restarting when Dusk swaps `.env` to `.env.dusk.local`.

Dusk tests use the macros in `tests/Pest.php` — always wait for Livewire
(`->waitForLivewire()->press('Save')`) rather than asserting immediately after a click.

## Style rules for tests

- One behaviour per test; name it as a sentence.
- Use factories, not raw `Model::create`.
- Assert values (`expect()->toBe()`), not implementation details.
- Never delete existing tests to make a change pass.
