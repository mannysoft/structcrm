<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\Services;

use App\Domains\CompanyManagement\DTOs\GettingStartedProgressData;
use App\Domains\CompanyManagement\DTOs\GettingStartedStepData;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Models\BoqItem;
use App\Domains\Finance\Models\Contract;
use App\Domains\Finance\Models\CostCode;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Invoice;
use App\Domains\Sales\Models\Supplier;
use Illuminate\Database\Eloquent\Model;

final class GettingStartedService
{
    /**
     * The step keys, in the recommended setup order.
     *
     * @var array<int, string>
     */
    public const STEP_KEYS = [
        'company_profile',
        'cost_codes',
        'clients',
        'suppliers',
        'projects',
        'contracts',
        'boq_items',
        'invoices',
    ];

    /**
     * @var array<string, array{title: string, description: string, route: string, action: string, model: class-string<Model>|null, requires: string|null}>
     */
    private const STEPS = [
        'company_profile' => [
            'title' => 'Complete your company profile',
            'description' => 'Add your email, phone, address, tax number and timezone so invoices, contracts and reports carry the correct details.',
            'route' => 'companies.settings',
            'action' => 'Update profile',
            'model' => null,
            'requires' => null,
        ],
        'cost_codes' => [
            'title' => 'Add your cost codes',
            'description' => 'Cost codes group BOQ items, budgets and expenses by work type such as labor, materials or equipment.',
            'route' => 'cost_codes.create',
            'action' => 'Add cost code',
            'model' => CostCode::class,
            'requires' => null,
        ],
        'clients' => [
            'title' => 'Add your first client',
            'description' => 'Every project belongs to a client, so add one before creating your first project.',
            'route' => 'clients.create',
            'action' => 'Add client',
            'model' => Client::class,
            'requires' => null,
        ],
        'suppliers' => [
            'title' => 'Add your first supplier',
            'description' => 'Suppliers are needed for purchase orders and to track where your project expenses come from.',
            'route' => 'suppliers.create',
            'action' => 'Add supplier',
            'model' => Supplier::class,
            'requires' => null,
        ],
        'projects' => [
            'title' => 'Create your first project',
            'description' => 'Projects hold your contracts, BOQ, budgets, progress billings and site records.',
            'route' => 'projects.create',
            'action' => 'Add project',
            'model' => Project::class,
            'requires' => 'clients',
        ],
        'contracts' => [
            'title' => 'Add your first contract',
            'description' => 'A contract records the original value that budgets, billings and profitability are measured against.',
            'route' => 'contracts.create',
            'action' => 'Add contract',
            'model' => Contract::class,
            'requires' => 'projects',
        ],
        'boq_items' => [
            'title' => 'Add your first BOQ items',
            'description' => 'The bill of quantities is the scope of work you bill progress against.',
            'route' => 'boq_items.create',
            'action' => 'Add BOQ item',
            'model' => BoqItem::class,
            'requires' => 'projects',
        ],
        'invoices' => [
            'title' => 'Create your first invoice',
            'description' => 'Bill completed work and track payments to keep your cash flow healthy.',
            'route' => 'invoices.create',
            'action' => 'Add invoice',
            'model' => Invoice::class,
            'requires' => 'projects',
        ],
    ];

    /**
     * @var array<string, string>
     */
    private const PROFILE_FIELDS = [
        'email' => 'email address',
        'phone' => 'phone number',
        'address' => 'address',
        'tax_number' => 'tax number',
        'timezone' => 'timezone',
    ];

    public function progress(Company $company): GettingStartedProgressData
    {
        return new GettingStartedProgressData(
            steps: $this->steps($company),
            isDismissed: $this->isDismissed($company),
        );
    }

    /**
     * @return array<int, GettingStartedStepData>
     */
    public function steps(Company $company): array
    {
        return array_map(
            fn (string $key): GettingStartedStepData => $this->buildStep($key, $company),
            self::STEP_KEYS,
        );
    }

    public function step(string $key, Company $company): ?GettingStartedStepData
    {
        if (! isset(self::STEPS[$key])) {
            return null;
        }

        return $this->buildStep($key, $company);
    }

    public function isStepComplete(string $key, Company $company): bool
    {
        if ($key === 'company_profile') {
            return $this->missingProfileDetails($company) === [];
        }

        $model = self::STEPS[$key]['model'] ?? null;

        if ($model === null) {
            return false;
        }

        return $model::query()->where('company_id', $company->id)->exists();
    }

    public function isDismissed(Company $company): bool
    {
        return $company->getting_started_dismissed_at !== null;
    }

    public function isComplete(Company $company): bool
    {
        foreach (self::STEP_KEYS as $key) {
            if (! $this->isStepComplete($key, $company)) {
                return false;
            }
        }

        return true;
    }

    public function dismiss(Company $company): void
    {
        $company->update(['getting_started_dismissed_at' => now()]);
    }

    public function restore(Company $company): void
    {
        $company->update(['getting_started_dismissed_at' => null]);
    }

    private function buildStep(string $key, Company $company): GettingStartedStepData
    {
        $step = self::STEPS[$key];

        return new GettingStartedStepData(
            key: $key,
            title: $step['title'],
            description: $step['description'],
            url: route($step['route']),
            actionLabel: $step['action'],
            isCompleted: $this->isStepComplete($key, $company),
            requires: $step['requires'],
            missingDetails: $key === 'company_profile' ? $this->missingProfileDetails($company) : [],
        );
    }

    /**
     * @return array<int, string>
     */
    private function missingProfileDetails(Company $company): array
    {
        $missing = [];

        foreach (self::PROFILE_FIELDS as $field => $label) {
            if (blank($company->{$field})) {
                $missing[] = $label;
            }
        }

        return $missing;
    }
}
