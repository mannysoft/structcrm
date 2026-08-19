<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\ClientUser;
use App\Domains\Sales\Notifications\ClientInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class InviteClientUserAction
{
    /**
     * Create (or re-invite) a client portal user and queue their invitation.
     */
    public function execute(Client $client, string $email): ClientUser
    {
        $clientUser = DB::transaction(function () use ($client, $email): ClientUser {
            $existing = ClientUser::withTrashed()
                ->where('company_id', $client->company_id)
                ->where('email', $email)
                ->first();

            if ($existing) {
                $existing->restore();

                $existing->forceFill([
                    'client_id' => $client->id,
                    'invited_at' => now(),
                ])->save();

                return $existing;
            }

            return ClientUser::create([
                'company_id' => $client->company_id,
                'client_id' => $client->id,
                'email' => $email,
                'password' => Str::random(40),
                'invited_at' => now(),
            ]);
        });

        $clientUser->notify(new ClientInvitation);

        return $clientUser;
    }
}
