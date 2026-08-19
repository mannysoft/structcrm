<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\CompanyManagement;

use App\Domains\CompanyManagement\Actions\CreateCompanyAndUserAction;
use App\Domains\CompanyManagement\DTOs\RegisterTenantData;
use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('create company and user action provisions new tenant', function () {

    $action = app(CreateCompanyAndUserAction::class);

    $dto = new RegisterTenantData(
        companyName: 'Stark Industries',
        userName: 'Tony Stark',
        email: 'tony@stark.com',
        password: 'password123',
    );

    $user = $action->execute($dto);

    $this->assertInstanceOf(User::class, $user);
    $this->assertSame('Tony Stark', $user->name);
    $this->assertSame('tony@stark.com', $user->email);
    $this->assertTrue(Hash::check('password123', $user->password));

    $company = Company::find($user->company_id);
    $this->assertNotNull($company);
    $this->assertSame('Stark Industries', $company->name);
});
