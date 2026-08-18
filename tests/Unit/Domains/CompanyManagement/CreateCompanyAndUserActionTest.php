<?php

declare(strict_types=1);

use App\Domains\CompanyManagement\Actions\CreateCompanyAndUserAction;
use App\Domains\CompanyManagement\DTOs\RegisterTenantData;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Domains\CompanyManagement\Models\Company;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

test('create company and user action provisions new tenant', function () {
    $action = app(CreateCompanyAndUserAction::class);
    
    $dto = new RegisterTenantData(
        companyName: 'Stark Industries',
        userName: 'Tony Stark',
        email: 'tony@stark.com',
        password: 'password123',
    );

    $user = $action->execute($dto);

    // Assert user created
    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('Tony Stark')
        ->and($user->email)->toBe('tony@stark.com')
        ->and(Hash::check('password123', $user->password))->toBeTrue();

    // Assert company created and linked
    $company = Company::find($user->company_id);
    expect($company)->not->toBeNull()
        ->and($company->name)->toBe('Stark Industries');
});
