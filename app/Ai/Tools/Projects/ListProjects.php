<?php

declare(strict_types=1);

namespace App\Ai\Tools\Projects;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListProjects implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List projects with status, client, manager and budget. Filter by status, client or a title/project-number search.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', Project::class);

            $search = trim((string) ($request['search'] ?? ''));
            $status = $request['status'] ?? null;
            $clientUuid = $request['client'] ?? null;

            $query = Project::query()
                ->with(['client', 'manager'])
                ->where('company_id', $this->companyId())
                ->when($search !== '', fn ($builder) => $builder->where(
                    fn ($inner) => $inner->where('title', 'like', "%{$search}%")->orWhere('project_number', 'like', "%{$search}%")
                ))
                ->when(filled($status), fn ($builder) => $builder->where('status', $status));

            if (filled($clientUuid)) {
                $client = $this->findRecord(Client::class, (string) $clientUuid, 'client');
                $query->where('client_id', $client->id);
            }

            $projects = $query->latest()->limit($this->limit($request['limit'] ?? null))->get();

            return [
                'count' => $projects->count(),
                'projects' => $projects->map(fn (Project $project) => $this->projectSummary($project))->all(),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Optional title or project number fragment.'),
            'status' => $schema->string()
                ->enum(array_column(ProjectStatus::cases(), 'value'))
                ->description('Optional project status filter.'),
            'client' => $schema->string()->description('Optional client UUID.'),
            'limit' => $schema->integer()->description('Maximum projects to return (default 25, max 100).'),
        ];
    }
}
