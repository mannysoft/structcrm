<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_leads_page_requires_authentication(): void
    {
        $this->get('/leads')->assertRedirect('/login');
    }

    public function test_leads_page_displays_leads(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        Lead::factory()->create(['company_id' => $company->id, 'first_name' => 'Jane']);

        $this->actingAs($user)
            ->get('/leads')
            ->assertOk()
            ->assertSee('Jane');
    }
}
