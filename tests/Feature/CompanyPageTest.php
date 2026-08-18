<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_companies_page_requires_authentication(): void
    {
        $this->get('/companies')->assertRedirect('/login');
    }

    public function test_companies_page_displays_the_current_company(): void
    {
        $company = Company::factory()->create(['name' => 'Acme Builders']);
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->get('/companies')
            ->assertOk()
            ->assertSee('Acme Builders');
    }
}
