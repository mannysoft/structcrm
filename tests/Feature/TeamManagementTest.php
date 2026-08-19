<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\URL;

test('team page requires authentication', function () {

    $this->get('/team')->assertRedirect('/login');
});

test('team page requires manage users permission', function () {

    $company = Company::factory()->create();
    $staff = User::factory()->role('staff')->create(['company_id' => $company->id]);

    $this->actingAs($staff)->get('/team')->assertForbidden();
});

test('owner can view the team page', function () {

    $company = Company::factory()->create();
    $owner = User::factory()->create(['company_id' => $company->id]);
    User::factory()->role('staff')->create(['company_id' => $company->id, 'name' => 'Jane Doe']);

    $this->actingAs($owner)
        ->get('/team')
        ->assertOk()
        ->assertSee('Jane Doe');
});

test('staff set password link requires a valid signature', function () {

    $company = Company::factory()->create();
    $user = User::factory()->role('staff')->create(['company_id' => $company->id]);

    $this->get('/staff/set-password/'.$user->id)->assertForbidden();

    $signed = URL::temporarySignedRoute(
        'staff.set-password',
        now()->addMinutes(5),
        ['user' => $user->id],
    );

    $this->get($signed)->assertOk();
});
