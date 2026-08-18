<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpensePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_expenses_page_requires_authentication(): void
    {
        $this->get('/expenses')->assertRedirect('/login');
    }

    public function test_expenses_page_displays_expenses(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
        $user = User::factory()->create(['company_id' => $company->id]);
        Expense::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $this->actingAs($user)
            ->get('/expenses')
            ->assertOk()
            ->assertSee($project->title);
    }
}
