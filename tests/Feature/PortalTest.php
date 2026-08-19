<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Enums\InvoiceStatus;
use App\Domains\Sales\Models\ChangeOrder;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\URL;

test('portal requires client authentication', function () {

    $this->get('/portal')->assertRedirect('/portal/login');
});

test('staff user cannot access portal', function () {

    $company = Company::factory()->create();
    $staff = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($staff)->get('/portal')->assertRedirect('/portal/login');
});

test('client sees only their projects', function () {

    $company = Company::factory()->create();
    $clientA = Client::factory()->create(['company_id' => $company->id]);
    $clientB = Client::factory()->create(['company_id' => $company->id]);

    Project::factory()->create(['company_id' => $company->id, 'client_id' => $clientA->id, 'title' => 'Alpha Project']);
    Project::factory()->create(['company_id' => $company->id, 'client_id' => $clientB->id, 'title' => 'Beta Project']);

    $clientUser = User::factory()->client()->create([
        'company_id' => $company->id,
        'client_id' => $clientA->id,
    ]);

    $this->actingAs($clientUser, 'client')
        ->get('/portal')
        ->assertOk()
        ->assertSee('Alpha Project')
        ->assertDontSee('Beta Project');
});

test('client does not see other clients change orders', function () {

    $company = Company::factory()->create();
    $clientA = Client::factory()->create(['company_id' => $company->id]);
    $clientB = Client::factory()->create(['company_id' => $company->id]);

    $projectA = Project::factory()->create(['company_id' => $company->id, 'client_id' => $clientA->id]);
    $projectB = Project::factory()->create(['company_id' => $company->id, 'client_id' => $clientB->id]);

    ChangeOrder::factory()->create(['company_id' => $company->id, 'project_id' => $projectA->id, 'status' => ChangeOrderStatus::Pending, 'description' => 'Alpha scope change']);
    ChangeOrder::factory()->create(['company_id' => $company->id, 'project_id' => $projectB->id, 'status' => ChangeOrderStatus::Pending, 'description' => 'Beta scope change']);

    Invoice::factory()->create(['company_id' => $company->id, 'project_id' => $projectA->id, 'status' => InvoiceStatus::Sent]);
    Invoice::factory()->create(['company_id' => $company->id, 'project_id' => $projectB->id, 'status' => InvoiceStatus::Sent]);

    $clientUser = User::factory()->client()->create([
        'company_id' => $company->id,
        'client_id' => $clientA->id,
    ]);

    $this->actingAs($clientUser, 'client')
        ->get('/portal')
        ->assertOk()
        ->assertSee('Alpha scope change')
        ->assertDontSee('Beta scope change');
});

test('client guard rejects staff credentials', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);

    User::factory()->create(['company_id' => $company->id, 'email' => 'staff@example.test']);
    User::factory()->client()->create(['company_id' => $company->id, 'client_id' => $client->id, 'email' => 'client@example.test']);

    $this->assertFalse(auth('client')->attempt([
        'email' => 'staff@example.test',
        'password' => 'password',
        'type' => User::TYPE_CLIENT,
    ]));

    $this->assertTrue(auth('client')->attempt([
        'email' => 'client@example.test',
        'password' => 'password',
        'type' => User::TYPE_CLIENT,
    ]));
});

test('set password link requires a valid signature', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $clientUser = User::factory()->client()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    $this->get('/portal/set-password/'.$clientUser->id)->assertForbidden();

    $signed = URL::temporarySignedRoute(
        'portal.set-password',
        now()->addMinutes(5),
        ['user' => $clientUser->id],
    );

    $this->get($signed)->assertOk();
});
