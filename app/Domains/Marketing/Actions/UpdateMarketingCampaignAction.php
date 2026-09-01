<?php

declare(strict_types=1);

namespace App\Domains\Marketing\Actions;

use App\Domains\Marketing\Models\MarketingCampaign;
use Illuminate\Support\Facades\DB;

final class UpdateMarketingCampaignAction
{
    public function __construct(
        private readonly SyncMarketingCampaignStepsAction $syncSteps,
    ) {}

    /**
     * @param  array<int, array{id?: int|null, subject: string, delay_days: int, body: string}>  $steps
     */
    public function execute(MarketingCampaign $campaign, string $name, ?string $description, bool $isActive, array $steps): MarketingCampaign
    {
        return DB::transaction(function () use ($campaign, $name, $description, $isActive, $steps) {
            $campaign->update([
                'name' => $name,
                'description' => $description,
                'is_active' => $isActive,
            ]);

            $this->syncSteps->execute($campaign, $steps);

            return $campaign;
        });
    }
}
