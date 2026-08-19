<?php

declare(strict_types=1);

namespace Tests\Unit\Common;

use App\Common\Scopes\TenantScope;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_scope_filters_models_to_the_authenticated_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        Client::factory()->create(['company_id' => $companyA->id, 'name' => 'Client A']);
        Client::factory()->create(['company_id' => $companyB->id, 'name' => 'Client B']);

        Supplier::factory()->create(['company_id' => $companyA->id]);
        Supplier::factory()->create(['company_id' => $companyB->id]);

        $this->actingAs(User::factory()->create(['company_id' => $companyA->id]));

        $this->assertSame(['Client A'], Client::query()->pluck('name')->all());
        $this->assertSame([$companyA->id], Supplier::query()->pluck('company_id')->unique()->all());
    }

    public function test_without_global_scope_reveals_cross_tenant_rows(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        Client::factory()->create(['company_id' => $companyA->id]);
        Client::factory()->create(['company_id' => $companyB->id]);

        $this->actingAs(User::factory()->create(['company_id' => $companyA->id]));

        $this->assertSame(1, Client::query()->count());
        $this->assertSame(2, Client::withoutGlobalScope(TenantScope::class)->count());
    }

    public function test_scope_is_a_no_op_without_an_authenticated_user(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        Client::factory()->create(['company_id' => $companyA->id]);
        Client::factory()->create(['company_id' => $companyB->id]);

        $this->assertSame(2, Client::query()->count());
    }

    public function test_project_queries_are_scoped_to_the_authenticated_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $clientA = Client::factory()->create(['company_id' => $companyA->id]);
        $clientB = Client::factory()->create(['company_id' => $companyB->id]);

        Project::factory()->create(['company_id' => $companyA->id, 'client_id' => $clientA->id, 'title' => 'Project A']);
        Project::factory()->create(['company_id' => $companyB->id, 'client_id' => $clientB->id, 'title' => 'Project B']);

        $this->actingAs(User::factory()->create(['company_id' => $companyA->id]));

        $this->assertSame(['Project A'], Project::query()->pluck('title')->all());
    }
}
