<?php

declare(strict_types=1);

namespace App\Ai\Tools\Analytics;

use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Finance\Models\Billing;
use App\Domains\Finance\Models\BillingPayment;
use App\Domains\Finance\Models\Contract;
use App\Domains\Finance\Services\ProjectProfitabilityService;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class GetDashboardSummary implements Tool
{
    use InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get the company dashboard KPIs: active projects, total contract value, outstanding and overdue billing, projected profit, cost variance and the projects with the weakest margins. Use this for "how is the business doing" questions.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function (): array {
            $companyId = $this->companyId();
            $currency = $this->currency();

            $projects = Project::query()->where('company_id', $companyId)->get();

            $activeStatuses = [
                ProjectStatus::Draft,
                ProjectStatus::Pending,
                ProjectStatus::Active,
                ProjectStatus::OnHold,
            ];

            $activeProjects = $projects
                ->filter(fn (Project $project) => in_array($project->status, $activeStatuses, true))
                ->count();

            $contractValue = (int) Contract::query()->where('company_id', $companyId)->sum('contract_value_amount');

            $netBilled = (int) Billing::query()->where('company_id', $companyId)->sum('net_amount');

            $paid = (int) BillingPayment::query()
                ->whereHas('billing', fn ($query) => $query->where('company_id', $companyId))
                ->sum('amount');

            $overdue = Billing::query()
                ->where('company_id', $companyId)
                ->whereNotNull('due_date')
                ->where('due_date', '<', today()->toDateString())
                ->with('payments')
                ->get()
                ->sum(fn (Billing $billing) => max(0, $billing->net_amount - $billing->payments->sum('amount')));

            $profitability = app(ProjectProfitabilityService::class);

            $projectedProfit = 0;
            $costVariance = 0;
            $margins = [];

            foreach ($projects as $project) {
                $data = $profitability->forProject($project);

                $projectedProfit += $data->projectedProfit;
                $costVariance += $data->costVariance;

                $margins[] = [
                    'project' => $project->title,
                    'project_uuid' => $project->uuid,
                    'projected_margin' => $data->projectedMargin,
                    'cost_variance' => $this->toDecimal($data->costVariance),
                ];
            }

            usort($margins, fn (array $left, array $right) => $left['projected_margin'] <=> $right['projected_margin']);

            return [
                'currency' => $currency,
                'active_projects' => $activeProjects,
                'total_projects' => $projects->count(),
                'total_contract_value' => $this->toDecimal($contractValue),
                'billed_to_date' => $this->toDecimal($netBilled),
                'collected_to_date' => $this->toDecimal($paid),
                'outstanding_billing' => $this->toDecimal($netBilled - $paid),
                'overdue_collections' => $this->toDecimal((int) $overdue),
                'projected_profit' => $this->toDecimal($projectedProfit),
                'total_cost_variance' => $this->toDecimal($costVariance),
                'projects_at_risk' => array_slice($margins, 0, 5),
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
