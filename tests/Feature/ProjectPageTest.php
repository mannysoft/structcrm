<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_projects_page_requires_authentication(): void
    {
        $this->get('/projects')->assertRedirect('/login');
    }

    public function test_projects_page_displays_projects(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create(['company_id' => $company->id]);
        Project::factory()->create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'title' => 'Skyline Tower',
        ]);

        $this->actingAs($user)
            ->get('/projects')
            ->assertOk()
            ->assertSee('Skyline Tower');
    }
}
