<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Notifications\ClientInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class InviteClientUserAction
{
    /**
     * Create (or re-invite) a client portal user and queue their invitation.
     */
    public function execute(Client $client, string $email): User
    {
        $user = DB::transaction(function () use ($client, $email): User {
            $existing = User::where('email', $email)->first();

            if ($existing) {
                $existing->update([
                    'company_id' => $client->company_id,
                    'client_id' => $client->id,
                    'type' => User::TYPE_CLIENT,
                ]);

                return $existing;
            }

            return User::create([
                'company_id' => $client->company_id,
                'client_id' => $client->id,
                'type' => User::TYPE_CLIENT,
                'name' => $client->name,
                'email' => $email,
                'password' => Str::random(40),
            ]);
        });

        $user->notify(new ClientInvitation);

        return $user;
    }
}
