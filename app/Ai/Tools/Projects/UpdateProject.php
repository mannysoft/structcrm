<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Common\ValueObjects\Money;
use App\Domains\ProjectManagement\Actions\UpdateProjectAction;
use App\Domains\ProjectManagement\DTOs\ProjectData;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdateProject implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a project. Only the fields supplied are changed: title, description, status, budget, client, manager, start and end dates.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'project' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'string'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'client' => ['nullable', 'string'],
            'start_date' => ['nullable', 'string'],
            'end_date' => ['nullable', 'string'],
            'manager' => ['nullable', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $project = $this->findRecord(Project::class, $validated['project'], 'project', ['project_number']);
            $this->authorize('update', $project);

            $status = $project->status;

            if (filled($validated['status'] ?? null)) {
                try {
                    $status = ProjectStatus::from((string) $validated['status']);
                } catch (Throwable) {
                    throw new ToolFailure(
                        'Unsupported project status. Use one of: '.implode(', ', array_column(ProjectStatus::cases(), 'value')).'.'
                    );
                }
            }

            $clientId = $project->client_id;

            if (filled($validated['client'] ?? null)) {
                $clientId = $this->findRecord(Client::class, $validated['client'], 'client', ['name', 'company_name'])->id;
            }

            $managerId = $project->manager_id;

            if (array_key_exists('manager', $validated) && filled($validated['manager'])) {
                $managerId = $this->findTeamMember($validated['manager'])->id;
            }

            $updated = app(UpdateProjectAction::class)->execute($project, new ProjectData(
                title: (string) ($validated['title'] ?? $project->title),
                clientId: $clientId,
                status: $status,
                budget: isset($validated['budget'])
                    ? new Money($this->toCents($validated['budget']), $project->budget_currency ?? $this->currency())
                    : new Money((int) $project->budget_amount, $project->budget_currency ?? $this->currency()),
                description: $validated['description'] ?? $project->description,
                managerId: $managerId,
                startDate: filled($validated['start_date'] ?? null)
                    ? $this->parseDate($validated['start_date'])
                    : $project->start_date,
                endDate: filled($validated['end_date'] ?? null)
                    ? $this->parseDate($validated['end_date'])
                    : $project->end_date,
            ));

            return [
                'message' => 'Project updated.',
                'project' => $this->projectSummary($updated->fresh(['client', 'manager'])),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'project' => $schema->string()->description('Project UUID or project number.')->required(),
            'title' => $schema->string()->description('New project title.'),
            'description' => $schema->string()->description('New description.'),
            'status' => $schema->string()
                ->enum(array_column(ProjectStatus::cases(), 'value'))
                ->description('New project status.'),
            'budget' => $schema->number()->description('New budget in major currency units.'),
            'client' => $schema->string()->description('Client UUID, name or company name to reassign.'),
            'start_date' => $schema->string()->description('New start date (YYYY-MM-DD).'),
            'end_date' => $schema->string()->description('New end date (YYYY-MM-DD).'),
            'manager' => $schema->string()->description('New manager email, name or user id.'),
        ];
    }
}
