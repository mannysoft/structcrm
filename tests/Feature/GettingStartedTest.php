<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Models\BoqItem;
use App\Domains\Finance\Models\Contract;
use App\Domains\Finance\Models\CostCode;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Invoice;
use App\Domains\Sales\Models\Supplier;
use App\Models\User;
use Livewire\Livewire;

/**
 * @return array{0: Company, 1: User}
 */
function createBareTenant(): array
{
    $company = Company::factory()->create([
        'email' => null,
        'phone' => null,
        'address' => null,
        'tax_number' => null,
        'timezone' => null,
    ]);

    return [$company, User::factory()->create(['company_id' => $company->id])];
}

test('dashboard shows the getting started checklist for a new company', function () {

    [$company, $user] = createBareTenant();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Getting started')
        ->assertSee('0 of 8 steps complete')
        ->assertSee('Add your first client')
        ->assertSee('Complete your company profile');
});

test('the checklist marks completed steps', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('3 of 8 steps complete');
});

test('the dashboard card is hidden once the checklist is dismissed', function () {

    [$company, $user] = createBareTenant();

    Livewire::actingAs($user)
        ->test('dashboard.getting-started')
        ->call('dismiss');

    expect($company->fresh()->getting_started_dismissed_at)->not->toBeNull();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertDontSee('Getting started');
});

test('the checklist can be restored from company settings', function () {

    $company = Company::factory()->create(['getting_started_dismissed_at' => now()]);
    $user = User::factory()->create(['company_id' => $company->id]);

    Livewire::actingAs($user)
        ->test('companies.settings')
        ->call('restoreGettingStarted');

    expect($company->fresh()->getting_started_dismissed_at)->toBeNull();
});

test('the getting started page lists every setup step', function () {

    [$company, $user] = createBareTenant();

    $this->actingAs($user)
        ->get('/getting-started')
        ->assertOk()
        ->assertSee('Checklist')
        ->assertSee('Complete your company profile')
        ->assertSee('Add your cost codes')
        ->assertSee('Add your first client')
        ->assertSee('Add your first supplier')
        ->assertSee('Create your first project')
        ->assertSee('Add your first contract')
        ->assertSee('Add your first BOQ items')
        ->assertSee('Create your first invoice')
        ->assertSee('Next step');
});

test('project creation is blocked until a client exists', function () {

    [$company, $user] = createBareTenant();

    $this->actingAs($user)
        ->get('/projects/create')
        ->assertRedirect(route('getting_started.index'))
        ->assertSessionHas('getting_started_notice', 'Before you can continue, add your first client.');
});

test('project creation is allowed once a client exists', function () {

    $company = Company::factory()->create();
    Client::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->get('/projects/create')
        ->assertOk();
});

test('invoice creation is blocked until a project exists', function () {

    $company = Company::factory()->create();
    Client::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->get('/invoices/create')
        ->assertRedirect(route('getting_started.index'))
        ->assertSessionHas('getting_started_notice', 'Before you can continue, create your first project.');
});

test('invoice creation is allowed once a project exists', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);
    $user = User::factory()->create(['company_id' => $company->id]);
    session(['active_project_id' => $project->id]);

    $this->actingAs($user)
        ->get('/invoices/create')
        ->assertOk();
});

test('the checklist disappears once every step is complete', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    CostCode::factory()->create(['company_id' => $company->id]);
    Supplier::factory()->create(['company_id' => $company->id]);
    Contract::factory()->create(['company_id' => $company->id, 'project_id' => $project->id]);
    BoqItem::factory()->create(['company_id' => $company->id, 'project_id' => $project->id]);
    Invoice::factory()->create(['company_id' => $company->id, 'project_id' => $project->id]);

    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertDontSee('Getting started');

    $this->actingAs($user)
        ->get('/getting-started')
        ->assertOk()
        ->assertSee("You're all set")
        ->assertSee('100%');
});

test('dismissing the checklist does not bypass the prerequisites', function () {

    $company = Company::factory()->create([
        'email' => null,
        'phone' => null,
        'address' => null,
        'tax_number' => null,
        'timezone' => null,
        'getting_started_dismissed_at' => now(),
    ]);
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->get('/projects/create')
        ->assertRedirect(route('getting_started.index'));
});
