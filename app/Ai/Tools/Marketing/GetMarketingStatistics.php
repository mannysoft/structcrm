<?php

declare(strict_types=1);

namespace App\Ai\Tools\Marketing;

use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\Marketing\Models\MarketingEmailLog;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class GetMarketingStatistics implements Tool
{
    use InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get marketing email statistics: total emails sent, sends in the last 30 days, per-campaign totals, and how many opted-in contacts exist.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function (): array {
            $this->authorizePermission('manage_marketing');

            $logs = MarketingEmailLog::query()->get();
            $campaigns = MarketingCampaign::query()->get(['id', 'name'])->keyBy('id');

            return [
                'total_emails_sent' => $logs->count(),
                'last_30_days' => $logs->where('sent_at', '>=', now()->subDays(30))->count(),
                'active_campaigns' => MarketingCampaign::query()->where('is_active', true)->count(),
                'opted_in_contacts' => User::query()->where('marketing_opt_in', true)->count(),
                'by_campaign' => $logs
                    ->groupBy('campaign_id')
                    ->map(fn ($group, $campaignId) => [
                        'campaign' => $campaigns->get($campaignId)?->name ?? 'Deleted campaign',
                        'sent' => $group->count(),
                        'last_sent_at' => $group->max('sent_at')?->toDateTimeString(),
                    ])
                    ->sortByDesc('sent')
                    ->values()
                    ->all(),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
