<?php

declare(strict_types=1);

namespace App\Domains\Sales\Policies;

use App\Domains\Sales\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Client $client): bool
    {
        return $user->company_id === $client->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasCapability('manage_clients');
    }

    public function update(User $user, Client $client): bool
    {
        return $user->company_id === $client->company_id && $user->hasCapability('manage_clients');
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->company_id === $client->company_id && $user->hasCapability('manage_clients');
    }
}
