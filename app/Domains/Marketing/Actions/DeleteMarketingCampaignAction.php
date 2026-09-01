<?php

declare(strict_types=1);

namespace App\Domains\Marketing\Actions;

use App\Domains\Marketing\Models\MarketingCampaign;

final class DeleteMarketingCampaignAction
{
    public function execute(MarketingCampaign $campaign): void
    {
        $campaign->delete();
    }
}
