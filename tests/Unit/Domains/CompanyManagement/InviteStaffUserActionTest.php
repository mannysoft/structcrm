<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\CompanyManagement;

use App\Domains\CompanyManagement\Actions\InviteStaffUserAction;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\CompanyManagement\Notifications\StaffInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

test('it creates a staff user with a role and queues an invitation', function () {

    Notification::fake();

    $company = Company::factory()->create();

    $user = app(InviteStaffUserAction::class)->execute($company->id, 'jane@example.test', 'project_manager');

    $this->assertFalse($user->isClient());
    $this->assertSame($company->id, $user->company_id);
    $this->assertTrue($user->hasRole('project_manager'));
    $this->assertNull($user->email_verified_at);

    Notification::assertSentTo($user, StaffInvitation::class);
});

test('it reassigns an existing user to the company and role', function () {

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
});
