<?php

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The base TestCase is applied to every test, and the database is refreshed
| and migrated before each test. Roles/permissions are seeded by the base
| TestCase's setUp().
|
*/

uses(
    TestCase::class,
    RefreshDatabase::class,
)->in('Feature', 'Unit');

uses(
    DuskTestCase::class,
)->in('Browser');

/*
|--------------------------------------------------------------------------
| Shared helpers
|--------------------------------------------------------------------------
*/

function makeProject(Company $company): Project
{
    $client = Client::factory()->create(['company_id' => $company->id]);

    return Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);
}

/**
 * Create a company and an owner staff user with a known password.
 *
 * @return array{0: Company, 1: User}
 */
function createStaffUser(): array
{
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => Hash::make('password'),
    ]);

    return [$company, $user];
}

/**
 * Log in through the staff login page.
 */
function loginAsStaff(Browser $browser, User $user): void
{
    // Dusk reuses the browser across tests in the same file, so clear the
    // session first to guarantee a logged-out state before signing in.
    $browser->visit('/login');
    $browser->driver->manage()->deleteAllCookies();
    $browser->visit('/login')
        ->assertSee('LOG IN')
        ->type('email', $user->email)
        ->type('password', 'password')
        ->press('LOG IN')
        ->waitForLocation('/dashboard');
}
