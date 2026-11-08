<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Common\ValueObjects\Money;
use App\Domains\ProjectManagement\Actions\CreateProjectAction;
use App\Domains\ProjectManagement\DTOs\ProjectData;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class CreateProject implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Create a project for an existing client. Requires a title, client identifier and budget. The project number is generated automatically.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'client' => ['required', 'string'],
            'budget' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:2000'],
            'start_date' => ['nullable', 'string'],
            'end_date' => ['nullable', 'string'],
            'manager' => ['nullable', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', Project::class);

            $client = $this->findRecord(Client::class, $validated['client'], 'client', ['name', 'company_name']);

            $status = $this->parseStatus($validated['status'] ?? null);
            $managerId = filled($validated['manager'] ?? null)
                ? $this->findTeamMember($validated['manager'])->id
                : null;

            $project = app(CreateProjectAction::class)->execute(
                new ProjectData(
                    title: $validated['title'],
                    clientId: $client->id,
                    status: $status,
                    budget: new Money($this->toCents($validated['budget']), $this->currency()),
                    description: $validated['description'] ?? null,
                    managerId: $managerId,
                    startDate: filled($validated['start_date'] ?? null) ? $this->parseDate($validated['start_date']) : null,
                    endDate: filled($validated['end_date'] ?? null) ? $this->parseDate($validated['end_date']) : null,
                ),
                $this->companyId(),
            );

            return [
                'message' => 'Project created.',
                'project' => $this->projectSummary($project->fresh(['client', 'manager'])),
            ];
        });
    }

    private function parseStatus(?string $status): ProjectStatus
    {
        if ($status === null || trim($status) === '') {
            return ProjectStatus::Draft;
        }

        try {
            return ProjectStatus::from($status);
        } catch (Throwable) {
            throw new ToolFailure(
                'Unsupported project status. Use one of: '.implode(', ', array_column(ProjectStatus::cases(), 'value')).'.'
            );
        }
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('Project title.')->required(),
            'client' => $schema->string()->description('Client UUID, name or company name.')->required(),
            'budget' => $schema->number()->description('Budget in major currency units, e.g. 1500000.50.')->required(),
            'status' => $schema->string()
                ->enum(array_column(ProjectStatus::cases(), 'value'))
                ->description('Project status (defaults to draft).'),
            'description' => $schema->string()->description('Optional project description.'),
            'start_date' => $schema->string()->description('Optional start date (YYYY-MM-DD).'),
            'end_date' => $schema->string()->description('Optional end date (YYYY-MM-DD).'),
            'manager' => $schema->string()->description('Optional project manager email, name or user id.'),
        ];
    }
}
