<?php

declare(strict_types=1);

namespace App\Domains\Marketing\Policies;

use App\Domains\Marketing\Models\MarketingCampaign;
use App\Models\User;

class MarketingCampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage_marketing');
    }

    public function view(User $user, MarketingCampaign $campaign): bool
    {
        return $user->hasPermissionTo('manage_marketing');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_marketing');
    }

    public function update(User $user, MarketingCampaign $campaign): bool
    {
        return $user->hasPermissionTo('manage_marketing');
    }

    public function delete(User $user, MarketingCampaign $campaign): bool
    {
        return $user->hasPermissionTo('manage_marketing');
    }
}
