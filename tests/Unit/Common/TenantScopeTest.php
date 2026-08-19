<?php

declare(strict_types=1);

namespace Tests\Unit\Common;

use App\Common\Scopes\TenantScope;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Supplier;
use App\Models\User;

test('global scope filters models to the authenticated company', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    Client::factory()->create(['company_id' => $companyA->id, 'name' => 'Client A']);
    Client::factory()->create(['company_id' => $companyB->id, 'name' => 'Client B']);

    Supplier::factory()->create(['company_id' => $companyA->id]);
    Supplier::factory()->create(['company_id' => $companyB->id]);

    $this->actingAs(User::factory()->create(['company_id' => $companyA->id]));

    $this->assertSame(['Client A'], Client::query()->pluck('name')->all());
    $this->assertSame([$companyA->id], Supplier::query()->pluck('company_id')->unique()->all());
});

test('without global scope reveals cross tenant rows', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    Client::factory()->create(['company_id' => $companyA->id]);
    Client::factory()->create(['company_id' => $companyB->id]);

    $this->actingAs(User::factory()->create(['company_id' => $companyA->id]));

    $this->assertSame(1, Client::query()->count());
    $this->assertSame(2, Client::withoutGlobalScope(TenantScope::class)->count());
});

test('scope is a no op without an authenticated user', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    Client::factory()->create(['company_id' => $companyA->id]);
    Client::factory()->create(['company_id' => $companyB->id]);

    $this->assertSame(2, Client::query()->count());
});

test('project queries are scoped to the authenticated company', function () {

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $clientA = Client::factory()->create(['company_id' => $companyA->id]);
    $clientB = Client::factory()->create(['company_id' => $companyB->id]);

    Project::factory()->create(['company_id' => $companyA->id, 'client_id' => $clientA->id, 'title' => 'Project A']);
    Project::factory()->create(['company_id' => $companyB->id, 'client_id' => $clientB->id, 'title' => 'Project B']);

    $this->actingAs(User::factory()->create(['company_id' => $companyA->id]));

    $this->assertSame(['Project A'], Project::query()->pluck('title')->all());
});
