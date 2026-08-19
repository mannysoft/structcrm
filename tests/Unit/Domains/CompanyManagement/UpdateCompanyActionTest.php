<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\CompanyManagement;

use App\Domains\CompanyManagement\Actions\UpdateCompanyAction;
use App\Domains\CompanyManagement\DTOs\CompanyData;
use App\Domains\CompanyManagement\Enums\Currency;
use App\Domains\CompanyManagement\Models\Company;

test('it updates a company profile', function () {

    $company = Company::factory()->create(['name' => 'Old Name']);

    $action = app(UpdateCompanyAction::class);

    $updated = $action->execute($company, new CompanyData(
        name: 'New Name',
        email: 'new@example.test',
        phone: '555-1234',
        address: '123 Main St',
        taxNumber: 'EIN-123',
        timezone: 'Europe/London',
        currency: Currency::GBP,
    ));

    $this->assertSame('New Name', $updated->name);
    $this->assertSame('Europe/London', $updated->timezone);
    $this->assertSame(Currency::GBP, $updated->currency);

    $this->assertDatabaseHas('companies', [
        'id' => $company->id,
        'name' => 'New Name',
    ]);
});
