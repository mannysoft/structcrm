<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Sales\Enums\LeadStatus;
use App\Domains\Sales\Models\Lead;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListLeads implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List sales leads with status and contact details. Filter by status or search by name, email or phone.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', Lead::class);

            $search = trim((string) ($request['search'] ?? ''));

            $leads = Lead::query()
                ->where('company_id', $this->companyId())
                ->when($search !== '', fn ($builder) => $builder->where(
                    fn ($inner) => $inner
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                ))
                ->when(filled($request['status'] ?? null), fn ($builder) => $builder->where('status', $request['status']))
                ->latest()
                ->limit($this->limit($request['limit'] ?? null))
                ->get();

            return [
                'count' => $leads->count(),
                'leads' => $leads->map(fn (Lead $lead) => $this->leadSummary($lead))->all(),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Optional name, email or phone fragment.'),
            'status' => $schema->string()->enum(array_column(LeadStatus::cases(), 'value'))->description('Optional status filter.'),
            'limit' => $schema->integer()->description('Maximum leads to return (default 25, max 100).'),
        ];
    }
}
