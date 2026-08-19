<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Lead;
use App\Models\User;

test('leads page requires authentication', function () {

    $this->get('/leads')->assertRedirect('/login');
});

test('leads page displays leads', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    Lead::factory()->create(['company_id' => $company->id, 'first_name' => 'Jane']);

    $this->actingAs($user)
        ->get('/leads')
        ->assertOk()
        ->assertSee('Jane');
});
