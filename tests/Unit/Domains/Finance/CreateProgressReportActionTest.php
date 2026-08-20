<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Finance;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Actions\CreateProgressReportAction;
use App\Domains\Finance\DTOs\ProgressReportData;
use App\Domains\Finance\Enums\ProgressReportStatus;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use Carbon\CarbonImmutable;

test('it creates a progress report with items', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    $report = app(CreateProgressReportAction::class)->execute(new ProgressReportData(
        projectId: $project->id,
        reportDate: CarbonImmutable::parse('2026-08-20'),
        status: ProgressReportStatus::Submitted,
        items: [
            ['description' => 'Foundation works', 'quantity' => 1, 'percent_complete' => 60, 'accomplished_amount' => 1_500_000],
            ['description' => 'Structural works', 'quantity' => 1, 'percent_complete' => 25, 'accomplished_amount' => 1_000_000],
        ],
    ), $company->id);

    $this->assertSame('REP-1', $report->report_number);
    $this->assertSame(2_500_000, $report->totalAccomplished());
    $this->assertCount(2, $report->items);

    $this->assertDatabaseHas('progress_items', [
        'progress_report_id' => $report->id,
        'description' => 'Foundation works',
    ]);
});
