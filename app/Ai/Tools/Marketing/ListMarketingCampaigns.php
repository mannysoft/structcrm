<?php

declare(strict_types=1);

namespace App\Ai\Tools\Marketing;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Marketing\Models\MarketingCampaign;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListMarketingCampaigns implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List marketing email campaigns with their active state and step sequence (subject and delay in days).';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', MarketingCampaign::class);

            $campaigns = MarketingCampaign::query()
                ->with('steps')
                ->when(filter_var($request['active_only'] ?? false, FILTER_VALIDATE_BOOLEAN), fn ($builder) => $builder->where('is_active', true))
                ->orderBy('name')
                ->limit($this->limit($request['limit'] ?? null))
                ->get();

            return [
                'count' => $campaigns->count(),
                'campaigns' => $campaigns->map(fn (MarketingCampaign $campaign) => $this->campaignSummary($campaign))->all(),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'active_only' => $schema->boolean()->description('When true, only active campaigns are returned.'),
            'limit' => $schema->integer()->description('Maximum campaigns to return (default 25, max 100).'),
        ];
    }
}
