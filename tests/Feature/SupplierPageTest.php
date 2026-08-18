<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_suppliers_page_requires_authentication(): void
    {
        $this->get('/suppliers')->assertRedirect('/login');
    }

    public function test_suppliers_page_displays_suppliers(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        Supplier::factory()->create(['company_id' => $company->id, 'name' => 'Acme Materials']);

        $this->actingAs($user)
            ->get('/suppliers')
            ->assertOk()
            ->assertSee('Acme Materials');
    }
}
