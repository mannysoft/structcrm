<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\CompanyManagement;

use App\Domains\CompanyManagement\Actions\InviteStaffUserAction;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\CompanyManagement\Notifications\StaffInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InviteStaffUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_staff_user_with_a_role_and_queues_an_invitation(): void
    {
        Notification::fake();

        $company = Company::factory()->create();

        $user = app(InviteStaffUserAction::class)->execute($company->id, 'jane@example.test', 'project_manager');

        $this->assertFalse($user->isClient());
        $this->assertSame($company->id, $user->company_id);
        $this->assertTrue($user->hasRole('project_manager'));
        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo($user, StaffInvitation::class);
    }

    public function test_it_reassigns_an_existing_user_to_the_company_and_role(): void
    {
        Notification::fake();

        $company = Company::factory()->create();
        $existing = User::factory()->role('staff')->create([
            'company_id' => $company->id,
            'email' => 'jane@example.test',
        ]);

        $user = app(InviteStaffUserAction::class)->execute($company->id, 'jane@example.test', 'foreman');

        $this->assertSame($existing->id, $user->id);
        $this->assertTrue($user->hasRole('foreman'));
        $this->assertFalse($user->hasRole('staff'));
    }
}
