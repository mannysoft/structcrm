---
title: Adding a module
sidebar_position: 13
description: A complete walkthrough for adding a new domain module the way this codebase does it.
---

# Adding a module

This walkthrough adds a hypothetical **Submittals** module (a project document that needs
approval). Follow the same order for any new module.

## 0. Decide the domain

| Question | Example answer |
| :--- | :--- |
| Which domain owns it? | `ProjectManagement` (project-scoped document) |
| Does it need a permission? | Yes — `manage_submittals` |
| Is it project-scoped? | Yes → use the project switcher filter and the getting-started gate |

## 1. Migration and model

```bash
php artisan make:migration create_submittals_table
```

```php
Schema::create('submittals', function (Blueprint $table) {
    $table->id();
    $table->uuid()->unique();
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->foreignId('project_id')->constrained()->cascadeOnDelete();
    $table->string('submittal_number')->nullable();
    $table->string('title');
    $table->string('status', 50);
    $table->text('notes')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

```php
final class Submittal extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = ['company_id', 'project_id', 'submittal_number', 'title', 'status', 'notes'];

    protected $casts = ['status' => SubmittalStatus::class];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
```

:::danger Always override `uniqueIds()`
`HasUuids` alone makes `uniqueIds()` return `['id']`, which tries to UUID the integer
primary key and breaks inserts. Return `['uuid']` explicitly — this is recorded in
`.ai/rules/domains.md`.
:::

## 2. Enum

```php
enum SubmittalStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
```

## 3. DTO

```php
final readonly class SubmittalData
{
    public function __construct(
        public int $projectId,
        public string $title,
        public SubmittalStatus $status,
        public ?string $notes = null,
    ) {}
}
```

## 4. Action

```php
final class CreateSubmittalAction
{
    public function execute(SubmittalData $data, int $companyId): Submittal
    {
        return DB::transaction(fn () => Submittal::create([
            'company_id' => $companyId,
            'project_id' => $data->projectId,
            'title' => $data->title,
            'status' => $data->status,
            'notes' => $data->notes,
        ]));
    }
}
```

## 5. Observer (if it needs a number)

```php
final class SubmittalObserver
{
    public function creating(Submittal $submittal): void
    {
        if (empty($submittal->submittal_number)) {
            $count = Submittal::withTrashed()->where('company_id', $submittal->company_id)->count();
            $submittal->submittal_number = sprintf('SUB-%d', $count + 1);
        }
    }
}
```

Attach with `#[ObservedBy([SubmittalObserver::class])]` on the model.

## 6. Policy

```php
class SubmittalPolicy
{
    public function view(User $user, Submittal $submittal): bool
    {
        return $user->company_id === $submittal->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_submittals');
    }

    // update/delete: company match + permission
}
```

## 7. Permission and role

```php
// RolePermissionSeeder::PERMISSIONS
'manage_submittals',
```

Add it to the roles that should have it (owner/administrator get everything automatically).
Then reseed: `php artisan db:seed --class=RolePermissionSeeder`.

## 8. Form requests

```php
final class StoreSubmittalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Submittal::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(SubmittalStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function toDTO(): SubmittalData { /* … */ }
}
```

## 9. API controller and resource

```php
final class SubmittalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $submittals = Submittal::where('company_id', $request->user()->company_id)->latest()->get();

        return SubmittalResource::collection($submittals)->response();
    }
}
```

Register in `routes/api.php` inside the existing `api.` name group:

```php
Route::apiResource('submittals', SubmittalController::class);
```

Expose the UUID, never the integer id:

```php
return ['id' => $this->uuid, 'title' => $this->title, 'status' => $this->status->value];
```

## 10. Web routes and component

```php
Route::livewire('submittals', 'submittals.index')->name('submittals.index');
Route::livewire('submittals/create', 'submittals.create')
    ->middleware('getting-started')
    ->name('submittals.create');
```

If you gate `create`, add the route name to
`EnsureGettingStartedPrerequisites::PREREQUISITES` and decide which step it requires.

## 11. Navigation

Add an entry to the `$navigation` array in
`resources/views/livewire/layout/navigation.blade.php`, with an icon key that exists in the
`$icons` map.

## 12. Factory, seeder, tests

```bash
php artisan make:factory SubmittalFactory
php artisan make:seeder SubmittalSeeder
php artisan make:test SubmittalTest
```

```php
it('creates a submittal for the company', function () {
    [$company, $user] = createStaffUser();
    $project = makeProject($company);

    $this->actingAs($user)
        ->post(route('api.submittals.store'), [
            'project_id' => $project->id,
            'title' => 'Rebar shop drawings',
            'status' => 'draft',
        ])
        ->assertCreated();
});
```

## 13. AI tools (optional)

Add `ListSubmittals`, `GetSubmittal`, `CreateSubmittal`, `UpdateSubmittal`,
`DeleteSubmittal` under `app/Ai/Tools/Projects`, register them in `CrmAssistant::tools()`,
and gate deletes behind `Approval::required()`.

## 14. Documentation

Add a page under `docs-site/docs/user-guide` and an endpoint section under
`docs-site/docs/api`, then run `npm run build` in `docs-site`.

## Checklist

- [ ] Migration, model, `uniqueIds()`, `getRouteKeyName()`
- [ ] Enum, DTO, action(s) in `DB::transaction`
- [ ] Observer for the document number
- [ ] Policy + permission + seeder
- [ ] Form requests with tenant-scoped `exists` rules
- [ ] API controller + resource (UUID ids)
- [ ] Routes (`web.php`, `api.php`), getting-started gate if needed
- [ ] Livewire pages + navigation entry
- [ ] Factory, seeder, feature tests (happy path, permission failure, cross-tenant)
- [ ] `vendor/bin/pint --dirty`
- [ ] Docs updated
