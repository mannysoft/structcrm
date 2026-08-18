<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Enums\EstimateStatus;
use App\Domains\Sales\Models\Estimate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimateControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_create_an_estimate_with_items(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)->postJson('/api/estimates', [
            'title' => 'Kitchen remodel',
            'status' => EstimateStatus::Draft->value,
            'tax_rate' => 10,
            'items' => [
                ['description' => 'Cabinets', 'quantity' => 1, 'unit_price' => 500.00],
            ],
        ])->assertCreated();

        $this->assertDatabaseHas('estimates', [
            'company_id' => $company->id,
            'amount' => 55000,
        ]);
    }

    public function test_a_user_can_list_their_estimates(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        Estimate::factory()->count(2)->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->getJson('/api/estimates')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_a_user_can_delete_their_estimate(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $estimate = Estimate::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->deleteJson('/api/estimates/'.$estimate->uuid)
            ->assertNoContent();

        $this->assertSoftDeleted('estimates', ['id' => $estimate->id]);
    }

    public function test_a_user_cannot_view_another_tenants_estimate(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $companyA->id]);
        $estimateB = Estimate::factory()->create(['company_id' => $companyB->id]);

        $this->actingAs($user)
            ->getJson('/api/estimates/'.$estimateB->uuid)
            ->assertNotFound();
    }
}
