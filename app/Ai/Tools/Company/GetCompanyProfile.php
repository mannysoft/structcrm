<?php

declare(strict_types=1);

namespace App\Ai\Tools\Company;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class GetCompanyProfile implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get the current company profile (name, contact details, tax number, timezone, currency) together with counts of team members, clients and projects.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function (): array {
            $company = Company::query()->find($this->companyId());

            if (! $company instanceof Company) {
                throw new ToolFailure('No company is associated with your account.');
            }

            return [
                'company' => $this->companySummary($company),
                'team_members' => User::query()
                    ->where('company_id', $company->id)
                    ->where('type', User::TYPE_STAFF)
                    ->count(),
                'clients' => Client::query()->where('company_id', $company->id)->count(),
                'projects' => Project::query()->where('company_id', $company->id)->count(),
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
