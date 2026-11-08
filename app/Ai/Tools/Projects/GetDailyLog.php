<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Models\DailyLog;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class GetDailyLog implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get one daily site log with the work performed, crew, labour hours, equipment usage, issues and safety incidents.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'log' => ['required', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $log = $this->findRecord(DailyLog::class, $validated['log'], 'daily log');
            $this->authorize('view', $log);

            return [
                'log' => $this->dailyLogSummary($log->load(['project', 'author'])),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'log' => $schema->string()->description('Daily log UUID.')->required(),
        ];
    }
}
