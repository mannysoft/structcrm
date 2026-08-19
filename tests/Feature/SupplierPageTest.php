<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Supplier;
use App\Models\User;

test('suppliers page requires authentication', function () {

    $this->get('/suppliers')->assertRedirect('/login');
});

test('suppliers page displays suppliers', function () {

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    Supplier::factory()->create(['company_id' => $company->id, 'name' => 'Acme Materials']);

    $this->actingAs($user)
        ->get('/suppliers')
        ->assertOk()
        ->assertSee('Acme Materials');
});
