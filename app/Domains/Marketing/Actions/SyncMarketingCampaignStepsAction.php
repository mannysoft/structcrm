<?php

declare(strict_types=1);

namespace App\Domains\Marketing\Actions;

use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\Marketing\Models\MarketingCampaignStep;

final class SyncMarketingCampaignStepsAction
{
    /**
     * Reconcile the campaign's steps with the given payload while preserving
     * send history for steps that are kept (matched by id).
     *
     * @param  array<int, array{id?: int|null, subject: string, delay_days: int, body: string}>  $steps
     */
    public function execute(MarketingCampaign $campaign, array $steps): void
    {
        $existingIds = $campaign->steps()->pluck('id')->all();
        $submittedIds = [];

        foreach ($steps as $index => $step) {
            $attributes = [
                'subject' => $step['subject'],
                'delay_days' => (int) $step['delay_days'],
                'body' => $step['body'],
                'sort_order' => $index,
            ];

            $stepId = isset($step['id']) ? (int) $step['id'] : null;

            if ($stepId !== null && in_array($stepId, $existingIds, true)) {
                MarketingCampaignStep::whereKey($stepId)->update($attributes);
                $submittedIds[] = $stepId;
            } else {
                $submittedIds[] = $campaign->steps()->create($attributes)->id;
            }
        }

        $removedIds = array_diff($existingIds, $submittedIds);

        if ($removedIds !== []) {
            $campaign->steps()->whereIn('id', $removedIds)->delete();
        }
    }
}
