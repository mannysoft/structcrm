<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
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
        $totalCostVariance = 0;
        $projectsAtRisk = [];

        foreach ($projects as $project) {
            $data = $profitability->forProject($project);

            $projectedProfit += $data->projectedProfit;
            $totalCostVariance += $data->costVariance;

            $projectsAtRisk[] = [
                'title' => $project->title,
                'margin' => $data->projectedMargin,
                'variance' => $data->costVariance,
            ];
        }

        $atRisk = collect($projectsAtRisk)->sortBy('margin')->take(5)->values();

        return [
            'activeProjects' => $activeProjects,
            'totalContractValue' => $totalContractValue,
            'outstandingBilling' => $outstandingBilling,
            'overdueCollections' => $overdueCollections,
            'projectedProfit' => $projectedProfit,
            'totalCostVariance' => $totalCostVariance,
            'currency' => $currency,
            'projectsAtRisk' => $atRisk,
        ];
    }
}; ?>

<div>
    <x-page-header title="Dashboard" description="Know your cost. Bill faster. Protect your profit." />

    <dl class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5">
            <div class="flex items-center gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                    </svg>
                </span>
                <dt class="text-sm font-medium text-gray-500">Active Projects</dt>
            </div>
            <dd class="mt-3 text-3xl font-semibold tracking-tight text-gray-900">{{ $activeProjects }}</dd>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5">
            <div class="flex items-center gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-600">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>
                </span>
                <dt class="text-sm font-medium text-gray-500">Contract Value</dt>
            </div>
            <dd class="mt-3 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($totalContractValue / 100, 2) }} <span class="text-base font-medium text-gray-500">{{ $currency }}</span></dd>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5">
            <div class="flex items-center gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75M15.75 18H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                    </svg>
                </span>
                <dt class="text-sm font-medium text-gray-500">Outstanding Billing</dt>
            </div>
            <dd class="mt-3 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($outstandingBilling / 100, 2) }} <span class="text-base font-medium text-gray-500">{{ $currency }}</span></dd>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5">
            <div class="flex items-center gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </span>
                <dt class="text-sm font-medium text-gray-500">Overdue Collections</dt>
            </div>
            <dd class="mt-3 text-3xl font-semibold tracking-tight text-red-600">{{ number_format($overdueCollections / 100, 2) }} <span class="text-base font-medium text-red-400">{{ $currency }}</span></dd>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5">
            <div class="flex items-center gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                    </svg>
                </span>
                <dt class="text-sm font-medium text-gray-500">Projected Profit</dt>
            </div>
            <dd class="mt-3 text-3xl font-semibold tracking-tight text-emerald-600">{{ number_format($projectedProfit / 100, 2) }} <span class="text-base font-medium text-emerald-400">{{ $currency }}</span></dd>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5">
            <div class="flex items-center gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg {{ $totalCostVariance < 0 ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }}">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </span>
                <dt class="text-sm font-medium text-gray-500">Cost Variance</dt>
            </div>
            <dd class="mt-3 text-3xl font-semibold tracking-tight {{ $totalCostVariance < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                {{ $totalCostVariance < 0 ? '-' : '' }}{{ number_format(abs($totalCostVariance) / 100, 2) }} <span class="text-base font-medium {{ $totalCostVariance < 0 ? 'text-red-400' : 'text-emerald-400' }}">{{ $currency }}</span>
            </dd>
        </div>
    </dl>

    <div class="mt-8 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
        <div class="px-4 py-5 sm:px-6">
            <h2 class="text-base font-semibold leading-7 text-gray-900">Projects at Risk</h2>
            <p class="mt-1 text-sm text-gray-500">Projects with the lowest projected margins.</p>
        </div>
        <div class="border-t border-gray-200">
            <ul role="list" class="divide-y divide-gray-200">
                @forelse ($projectsAtRisk as $project)
                    <li class="flex items-center justify-between px-4 py-4 sm:px-6">
                        <span class="text-sm font-medium text-gray-900">{{ $project['title'] }}</span>
                        <span class="flex items-center gap-4 text-sm">
                            <span class="text-xs font-medium {{ $project['variance'] < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                {{ $project['variance'] < 0 ? '-' : '' }}{{ number_format(abs($project['variance']) / 100, 2) }} {{ $currency }} variance
                            </span>
                            <span class="font-semibold {{ $project['margin'] < 10 ? 'text-red-600' : 'text-amber-600' }}">
                                {{ number_format($project['margin'], 1) }}%
                            </span>
                        </span>
                    </li>
                @empty
                    <li class="px-4 py-6 text-sm text-center text-gray-500 sm:px-6">No projects yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
