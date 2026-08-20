<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Models\Billing;
use App\Domains\Finance\Models\BillingPayment;
use App\Domains\Finance\Models\Contract;
use App\Domains\Finance\Services\ProjectProfitabilityService;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Domains\ProjectManagement\Models\Project;

new #[Layout('layouts.app')] class extends Component {
    public function with(ProjectProfitabilityService $profitability): array
    {
        $companyId = auth()->user()->company_id;
        $currency = Company::find($companyId)?->currency?->value ?? 'PHP';

        $projects = Project::where('company_id', $companyId)->get();

        $totalContractValue = (int) Contract::where('company_id', $companyId)->sum('contract_value_amount');

        $totalNet = (int) Billing::where('company_id', $companyId)->sum('net_amount');
        $totalPaid = (int) BillingPayment::whereHas('billing', fn ($query) => $query->where('company_id', $companyId))->sum('amount');
        $outstandingBilling = $totalNet - $totalPaid;

        $overdueCollections = Billing::where('company_id', $companyId)
            ->whereNotNull('due_date')
            ->where('due_date', '<', today()->toDateString())
            ->with('payments')
            ->get()
            ->sum(fn (Billing $billing) => max(0, $billing->net_amount - $billing->payments->sum('amount')));

        $activeStatuses = [ProjectStatus::Draft, ProjectStatus::Pending, ProjectStatus::Active, ProjectStatus::OnHold];
        $activeProjects = $projects->filter(fn (Project $project) => in_array($project->status, $activeStatuses, true))->count();

        $projectedProfit = 0;
        $projectsAtRisk = [];

        foreach ($projects as $project) {
            $data = $profitability->forProject($project);

            $projectedProfit += $data->projectedProfit;

            $projectsAtRisk[] = [
                'title' => $project->title,
                'margin' => $data->projectedMargin,
            ];
        }

        $atRisk = collect($projectsAtRisk)->sortBy('margin')->take(5)->values();

        return [
            'activeProjects' => $activeProjects,
            'totalContractValue' => $totalContractValue,
            'outstandingBilling' => $outstandingBilling,
            'overdueCollections' => $overdueCollections,
            'projectedProfit' => $projectedProfit,
            'currency' => $currency,
            'projectsAtRisk' => $atRisk,
        ];
    }
}; ?>

<div>
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">Dashboard</h1>
            <p class="mt-2 text-sm text-gray-700">Know your cost. Bill faster. Protect your profit.</p>
        </div>
    </div>

    <dl class="grid grid-cols-1 gap-5 mt-8 sm:grid-cols-2 lg:grid-cols-3">
        <div class="overflow-hidden bg-white rounded-lg shadow ring-1 ring-black ring-opacity-5">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Active Projects</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $activeProjects }}</dd>
            </div>
        </div>
        <div class="overflow-hidden bg-white rounded-lg shadow ring-1 ring-black ring-opacity-5">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Contract Value</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($totalContractValue / 100, 2) }} {{ $currency }}</dd>
            </div>
        </div>
        <div class="overflow-hidden bg-white rounded-lg shadow ring-1 ring-black ring-opacity-5">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Outstanding Billing</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($outstandingBilling / 100, 2) }} {{ $currency }}</dd>
            </div>
        </div>
        <div class="overflow-hidden bg-white rounded-lg shadow ring-1 ring-black ring-opacity-5">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Overdue Collections</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-red-600">{{ number_format($overdueCollections / 100, 2) }} {{ $currency }}</dd>
            </div>
        </div>
        <div class="overflow-hidden bg-white rounded-lg shadow ring-1 ring-black ring-opacity-5">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Projected Profit</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-green-700">{{ number_format($projectedProfit / 100, 2) }} {{ $currency }}</dd>
            </div>
        </div>
    </dl>

    <div class="mt-8 overflow-hidden bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h2 class="text-base font-semibold leading-7 text-gray-900">Projects at Risk</h2>
            <p class="mt-1 text-sm text-gray-500">Projects with the lowest projected margins.</p>
        </div>
        <div class="border-t border-gray-200">
            <ul role="list" class="divide-y divide-gray-200">
                @forelse ($projectsAtRisk as $project)
                    <li class="flex items-center justify-between px-4 py-4 sm:px-6">
                        <span class="text-sm font-medium text-gray-900">{{ $project['title'] }}</span>
                        <span class="text-sm {{ $project['margin'] < 10 ? 'text-red-600' : 'text-amber-600' }}">
                            {{ number_format($project['margin'], 1) }}%
                        </span>
                    </li>
                @empty
                    <li class="px-4 py-6 text-sm text-center text-gray-500 sm:px-6">No projects yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
