<?php

declare(strict_types=1);

namespace App\Ai\Tools\Marketing;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Marketing\Actions\UpdateMarketingCampaignAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class UpdateMarketingCampaign implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a marketing campaign name, description, active state or step sequence. When steps are supplied they replace the existing sequence.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'campaign' => ['required', 'string'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'steps' => ['nullable', 'array'],
            'steps.*.subject' => ['required_with:steps', 'string', 'max:255'],
            'steps.*.body' => ['required_with:steps', 'string'],
            'steps.*.delay_days' => ['nullable', 'integer', 'min:0'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $campaign = $this->findMarketingCampaign($validated['campaign']);
            $this->authorize('update', $campaign);

            $existingSteps = $campaign->steps->map(fn ($step) => [
                'subject' => $step->subject,
                'body' => $step->body,
                'delay_days' => $step->delay_days,
                'sort_order' => $step->sort_order,
            ])->all();

            $steps = filled($validated['steps'] ?? null)
                ? collect($validated['steps'])->values()->map(fn (array $step, int $index) => [
                    'subject' => $step['subject'],
                    'body' => $step['body'],
                    'delay_days' => (int) ($step['delay_days'] ?? 0),
                    'sort_order' => $index + 1,
                ])->all()
                : $existingSteps;

            $updated = app(UpdateMarketingCampaignAction::class)->execute(
                $campaign,
                (string) ($validated['name'] ?? $campaign->name),
                $validated['description'] ?? $campaign->description,
                array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : (bool) $campaign->is_active,
                $steps,
            );

            return [
                'message' => 'Marketing campaign updated.',
                'campaign' => $this->campaignSummary($updated->fresh('steps')),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'campaign' => $schema->string()->description('Campaign UUID or exact name.')->required(),
            'name' => $schema->string()->description('New campaign name.'),
            'description' => $schema->string()->description('New description.'),
            'is_active' => $schema->boolean()->description('Activate or pause the campaign.'),
            'steps' => $schema->array()->items($schema->object([
                'subject' => $schema->string()->description('Email subject.')->required(),
                'body' => $schema->string()->description('Email body.')->required(),
                'delay_days' => $schema->integer()->description('Days after the previous step.')->min(0),
            ]))->description('Replacement step sequence.'),
        ];
    }
}
