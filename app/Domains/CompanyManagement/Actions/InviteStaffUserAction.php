<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\Actions;

use App\Domains\CompanyManagement\Notifications\StaffInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class InviteStaffUserAction
{
    /**
     * Create (or re-invite) a staff user, assign their role, and queue the invitation.
     */
    public function execute(int $companyId, string $email, string $role): User
    {
        $user = DB::transaction(function () use ($companyId, $email, $role): User {
            $existing = User::where('email', $email)->first();

            if ($existing) {
                $existing->update([
                    'company_id' => $companyId,
                    'type' => User::TYPE_STAFF,
                ]);

                $existing->syncRoles([$role]);

                return $existing;
            }

            $user = User::create([
                'company_id' => $companyId,
                'type' => User::TYPE_STAFF,
                'name' => Str::before($email, '@'),
                'email' => $email,
                'password' => Str::random(40),
            ]);

            $user->assignRole($role);

            return $user;
        });

        $user->notify(new StaffInvitation);

        return $user;
    }
}
