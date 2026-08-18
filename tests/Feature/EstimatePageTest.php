<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Estimate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimatePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_estimates_page_requires_authentication(): void
    {
        $this->get('/estimates')->assertRedirect('/login');
    }

    public function test_estimates_page_displays_estimates(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        Estimate::factory()->create([
            'company_id' => $company->id,
            'title' => 'Kitchen remodel',
        ]);

        $this->actingAs($user)
            ->get('/estimates')
            ->assertOk()
            ->assertSee('Kitchen remodel');
    }
}
