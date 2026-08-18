<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\ChangeOrder;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangeOrderPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_change_orders_page_requires_authentication(): void
    {
        $this->get('/change-orders')->assertRedirect('/login');
    }

    public function test_change_orders_page_displays_change_orders(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $user = User::factory()->create(['company_id' => $company->id]);
        ChangeOrder::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'description' => 'Extra concrete work',
        ]);

        $this->actingAs($user)
            ->get('/change-orders')
            ->assertOk()
            ->assertSee('Extra concrete work');
    }
}
