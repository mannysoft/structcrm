<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Enums\LeadStatus;
use App\Domains\Sales\Models\Lead;
use App\Models\User;

test('a user can create a lead', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->postJson('/api/leads', [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'status' => LeadStatus::New->value,
    ])->assertCreated();

    $this->assertDatabaseHas('leads', [
        'company_id' => $company->id,
        'first_name' => 'Jane',
    ]);
});

test('a user can list their leads', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    Lead::factory()->count(2)->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->getJson('/api/leads')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('a user can update their lead', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $lead = Lead::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->putJson('/api/leads/'.$lead->uuid, [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'status' => LeadStatus::Qualified->value,
        ])
        ->assertOk()
        ->assertJsonPath('first_name', 'Updated');
});

test('a user can delete their lead', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $lead = Lead::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->deleteJson('/api/leads/'.$lead->uuid)
        ->assertNoContent();

    $this->assertSoftDeleted('leads', ['id' => $lead->id]);
});

test('a user cannot view another tenants lead', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $companyA->id]);
    $leadB = Lead::factory()->create(['company_id' => $companyB->id]);

    $this->actingAs($user)
        ->getJson('/api/leads/'.$leadB->uuid)
        ->assertNotFound();
});
