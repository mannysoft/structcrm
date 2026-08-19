<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\CompanyManagement;

use App\Domains\CompanyManagement\Actions\UpdateStaffUserRoleAction;
use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;

test('it replaces the users role', function () {

    $company = Company::factory()->create();
    $user = User::factory()->role('staff')->create(['company_id' => $company->id]);

    app(UpdateStaffUserRoleAction::class)->execute($user, 'accountant');

    $this->assertTrue($user->fresh()->hasRole('accountant'));
    $this->assertFalse($user->fresh()->hasRole('staff'));
});
