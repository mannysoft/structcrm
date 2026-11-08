<?php

declare(strict_types=1);

namespace App\Ai\Tools\Marketing;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\Marketing\Models\MarketingEmailLog;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListMarketingEmailLogs implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List sent marketing campaign emails with the recipient, campaign, subject and sent date. Filter by campaign or recipient search.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', MarketingCampaign::class);

            $campaignId = filled($request['campaign'] ?? null)
                ? $this->findMarketingCampaign((string) $request['campaign'])->id
                : null;

            $search = trim((string) ($request['search'] ?? ''));

            $logs = MarketingEmailLog::query()
                ->with(['campaign', 'user'])
                ->when($campaignId !== null, fn ($query) => $query->where('campaign_id', $campaignId))
                ->when($search !== '', fn ($query) => $query->where(
                    fn ($inner) => $inner->where('subject', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($user) => $user->where('email', 'like', "%{$search}%"))
                ))
                ->latest('sent_at')
                ->limit($this->limit($request['limit'] ?? null))
                ->get();

            return [
                'count' => $logs->count(),
                'emails' => $logs->map(fn (MarketingEmailLog $log) => [
                    'uuid' => $log->uuid,
                    'campaign' => $log->campaign?->name,
                    'subject' => $log->subject,
                    'recipient' => $log->user?->email,
                    'sent_at' => $log->sent_at?->toDateTimeString(),
                ])->all(),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'campaign' => $schema->string()->description('Optional campaign UUID or exact name.'),
            'search' => $schema->string()->description('Optional subject or recipient email fragment.'),
            'limit' => $schema->integer()->description('Maximum emails to return (default 25, max 100).'),
        ];
    }
}
