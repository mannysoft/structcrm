<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Enums\LeadStatus;
use App\Domains\Sales\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_create_a_lead(): void
    {
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
    }

    public function test_a_user_can_list_their_leads(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        Lead::factory()->count(2)->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->getJson('/api/leads')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_a_user_can_update_their_lead(): void
    {
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
    }

    public function test_a_user_can_delete_their_lead(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $lead = Lead::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->deleteJson('/api/leads/'.$lead->uuid)
            ->assertNoContent();

        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
    }

    public function test_a_user_cannot_view_another_tenants_lead(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $companyA->id]);
        $leadB = Lead::factory()->create(['company_id' => $companyB->id]);

        $this->actingAs($user)
            ->getJson('/api/leads/'.$leadB->uuid)
            ->assertNotFound();
    }
}
