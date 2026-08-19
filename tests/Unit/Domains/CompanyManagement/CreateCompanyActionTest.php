<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\CompanyManagement;

use App\Domains\CompanyManagement\Actions\CreateCompanyAction;
use App\Domains\CompanyManagement\DTOs\CompanyData;
use App\Domains\CompanyManagement\Enums\Currency;
use App\Domains\CompanyManagement\Events\CompanyCreated;
use Illuminate\Support\Facades\Event;

test('it creates a company with a uuid and slug', function () {

    Event::fake([CompanyCreated::class]);

    $action = app(CreateCompanyAction::class);

    $company = $action->execute(new CompanyData(
        name: 'Acme Construction',
        email: 'hello@acme.test',
        currency: Currency::USD,
        timezone: 'America/New_York',
    ));

    $this->assertNotNull($company->uuid);
    $this->assertSame('acme-construction', $company->slug);
    $this->assertSame('hello@acme.test', $company->email);
    $this->assertSame(Currency::USD, $company->currency);

    $this->assertDatabaseHas('companies', [
        'id' => $company->id,
        'name' => 'Acme Construction',
    ]);

    Event::assertDispatched(CompanyCreated::class);
});
