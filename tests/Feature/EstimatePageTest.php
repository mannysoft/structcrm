<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Estimate;
use App\Models\User;

test('estimates page requires authentication', function () {

    $this->get('/estimates')->assertRedirect('/login');
});

test('estimates page displays estimates', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    Estimate::factory()->create([
        'company_id' => $company->id,
        'title' => 'Kitchen remodel',
    ]);

    $this->actingAs($user)
        ->get('/estimates')
        ->assertOk()
        ->assertSee('Kitchen remodel');
});
