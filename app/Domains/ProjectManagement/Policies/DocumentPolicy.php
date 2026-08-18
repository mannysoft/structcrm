<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Policies;

use App\Domains\ProjectManagement\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Document $document): bool
    {
        return $user->company_id === $document->company_id;
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function update(User $user, Document $document): bool
    {
        return $user->company_id === $document->company_id;
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->company_id === $document->company_id;
    }
}
