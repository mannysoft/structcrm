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

/**
 * Set an input's value via JavaScript and dispatch input/change events so
 * Livewire picks up the change. Useful for date/datetime inputs that are
 * flaky to drive with native keystrokes in headless Chrome.
 */
Browser::macro('setInputValue', function (string $selector, string $value) {
    $this->script("var el = document.querySelector('{$selector}'); if (!el) { throw new Error('Element not found: {$selector}'); } el.value = '{$value}'; el.dispatchEvent(new Event('input', { bubbles: true })); el.dispatchEvent(new Event('change', { bubbles: true }));");

    return $this;
});

/**
 * Click a repeater "Add" button and fill the newly rendered row using
 * Alpine x-model CSS selectors (e.g. input[x-model="item.description"]).
 *
 * @param  array<string, string>  $fields
 */
Browser::macro('addRepeaterItem', function (string $buttonLabel, array $fields) {
    $this->press($buttonLabel);

    foreach ($fields as $selector => $value) {
        $this->type($selector, $value);
    }

    return $this;
});

/**
 * Write a temporary file and return its absolute path.
 */
function createTempFile(string $filename, string $contents): string
{
    $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.$filename;

    file_put_contents($path, $contents);

    return $path;
}

/**
 * Create a tiny 1x1 PNG for image uploads.
 */
function createTempImage(string $filename): string
{
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');

    return createTempFile($filename, $png);
}

/**
 * Create a temporary CSV file for BOQ imports.
 *
 * @param  array<int, array<int, string>>  $rows
 */
function createTempCsv(string $filename, array $rows): string
{
    $stream = fopen('php://temp', 'w+');

    foreach ($rows as $row) {
        fputcsv($stream, $row);
    }

    rewind($stream);
    $contents = stream_get_contents($stream);
    fclose($stream);

    return createTempFile($filename, $contents);
}
