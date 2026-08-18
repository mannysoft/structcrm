<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\PurchaseOrder;
use App\Domains\Sales\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_orders_page_requires_authentication(): void
    {
        $this->get('/purchase-orders')->assertRedirect('/login');
    }

    public function test_purchase_orders_page_displays_purchase_orders(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $supplier = Supplier::factory()->create(['company_id' => $company->id, 'name' => 'Acme Materials']);
        $user = User::factory()->create(['company_id' => $company->id]);
        PurchaseOrder::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'supplier_id' => $supplier->id,
        ]);

        $this->actingAs($user)
            ->get('/purchase-orders')
            ->assertOk()
            ->assertSee('Acme Materials');
    }
}
