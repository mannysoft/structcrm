<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\CompanyManagement;

use App\Domains\CompanyManagement\Actions\UpdateStaffUserRoleAction;
use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateStaffUserRoleActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_replaces_the_users_role(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->role('staff')->create(['company_id' => $company->id]);

        app(UpdateStaffUserRoleAction::class)->execute($user, 'accountant');

        $this->assertTrue($user->fresh()->hasRole('accountant'));
        $this->assertFalse($user->fresh()->hasRole('staff'));
    }
}
