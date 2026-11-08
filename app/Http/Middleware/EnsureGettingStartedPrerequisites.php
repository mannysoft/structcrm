<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\CompanyManagement\Services\GettingStartedService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureGettingStartedPrerequisites
{
    /**
     * Create routes mapped to the checklist step they depend on.
     *
     * @var array<string, string>
     */
    private const PREREQUISITES = [
        'projects.create' => 'clients',
        'contracts.create' => 'projects',
        'boq_items.create' => 'projects',
        'boq_items.import' => 'projects',
        'budgets.create' => 'projects',
        'billings.create' => 'projects',
        'invoices.create' => 'projects',
        'change_orders.create' => 'projects',
        'expenses.create' => 'projects',
        'tasks.create' => 'projects',
        'daily_logs.create' => 'projects',
        'progress_reports.create' => 'projects',
        'project_photos.create' => 'projects',
        'documents.create' => 'projects',
        'time_entries.create' => 'projects',
        'purchase_orders.create' => 'projects',
    ];

    public function __construct(private readonly GettingStartedService $gettingStarted) {}

    public function handle(Request $request, Closure $next): Response
    {
        $requiredKey = self::PREREQUISITES[$request->route()?->getName()] ?? null;

        if ($requiredKey === null) {
            return $next($request);
        }

        $company = $request->user()?->company;

        if (! $company instanceof Company) {
            return $next($request);
        }

        if ($this->gettingStarted->isStepComplete($requiredKey, $company)) {
            return $next($request);
        }

        $step = $this->gettingStarted->step($requiredKey, $company);

        $message = $step !== null
            ? 'Before you can continue, '.lcfirst($step->title).'.'
            : 'Finish the getting started steps before you can continue.';

        return redirect()
            ->route('getting_started.index')
            ->with('getting_started_notice', $message);
    }
}
