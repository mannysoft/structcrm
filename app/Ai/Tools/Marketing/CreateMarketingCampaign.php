<?php

declare(strict_types=1);

namespace App\Ai\Tools\Marketing;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Marketing\Actions\CreateMarketingCampaignAction;
use App\Domains\Marketing\Models\MarketingCampaign;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class CreateMarketingCampaign implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Create a drip marketing email campaign with ordered steps. Each step has a subject, body and delay in days after the previous step.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.subject' => ['required', 'string', 'max:255'],
            'steps.*.body' => ['required', 'string'],
            'steps.*.delay_days' => ['nullable', 'integer', 'min:0'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', MarketingCampaign::class);

            $steps = collect($validated['steps'])
                ->values()
                ->map(fn (array $step, int $index) => [
                    'subject' => $step['subject'],
                    'body' => $step['body'],
                    'delay_days' => (int) ($step['delay_days'] ?? 0),
                    'sort_order' => $index + 1,
                ])
                ->all();

            $campaign = app(CreateMarketingCampaignAction::class)->execute(
                $validated['name'],
                $validated['description'] ?? null,
                (bool) ($validated['is_active'] ?? false),
                $steps,
            );

            return [
                'message' => 'Marketing campaign created.',
                'campaign' => $this->campaignSummary($campaign->fresh('steps')),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Campaign name.')->required(),
            'description' => $schema->string()->description('Optional internal description.'),
            'is_active' => $schema->boolean()->description('Whether the campaign sends emails (defaults to false).'),
            'steps' => $schema->array()->items($schema->object([
                'subject' => $schema->string()->description('Email subject.')->required(),
                'body' => $schema->string()->description('Email body.')->required(),
                'delay_days' => $schema->integer()->description('Days after the previous step.')->min(0),
            ]))->description('Ordered campaign steps.')->required(),
        ];
    }
}
