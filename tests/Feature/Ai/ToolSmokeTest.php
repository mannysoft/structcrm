<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Ai\Tools\Analytics\GetDashboardSummary;
use App\Ai\Tools\Analytics\GetExpenseBreakdown;
use App\Ai\Tools\Analytics\GetFinancialOverview;
use App\Ai\Tools\Analytics\SearchRecords;
use App\Ai\Tools\Company\GetCompanyProfile;
use App\Ai\Tools\Company\ListTeamMembers;
use App\Ai\Tools\Finance\ListBillings;
use App\Ai\Tools\Finance\ListBoqItems;
use App\Ai\Tools\Finance\ListBudgets;
use App\Ai\Tools\Finance\ListContracts;
use App\Ai\Tools\Finance\ListCostCodes;
use App\Ai\Tools\Finance\ListProgressReports;
use App\Ai\Tools\Marketing\GetMarketingStatistics;
use App\Ai\Tools\Marketing\ListMarketingCampaigns;
use App\Ai\Tools\Projects\ListDailyLogs;
use App\Ai\Tools\Projects\ListDocuments;
use App\Ai\Tools\Projects\ListEquipment;
use App\Ai\Tools\Projects\ListMaintenanceLogs;
use App\Ai\Tools\Projects\ListProjectPhotos;
use App\Ai\Tools\Projects\ListProjects;
use App\Ai\Tools\Projects\ListTasks;
use App\Ai\Tools\Projects\ListTimeEntries;
use App\Ai\Tools\Sales\ListChangeOrders;
use App\Ai\Tools\Sales\ListClients;
use App\Ai\Tools\Sales\ListEstimates;
use App\Ai\Tools\Sales\ListExpenses;
use App\Ai\Tools\Sales\ListInvoices;
use App\Ai\Tools\Sales\ListLeads;
use App\Ai\Tools\Sales\ListPurchaseOrders;
use App\Ai\Tools\Sales\ListSuppliers;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Laravel\Ai\Tools\Request;

test('every read-only tool executes successfully for an administrator', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $tools = [
        new ListProjects($user),
        new ListTasks($user),
        new ListDailyLogs($user),
        new ListTimeEntries($user),
        new ListDocuments($user),
        new ListProjectPhotos($user),
        new ListEquipment($user),
        new ListMaintenanceLogs($user),
        new ListClients($user),
        new ListLeads($user),
        new ListEstimates($user),
        new ListInvoices($user),
        new ListChangeOrders($user),
        new ListPurchaseOrders($user),
        new ListSuppliers($user),
        new ListExpenses($user),
        new ListCostCodes($user),
        new ListContracts($user),
        new ListBoqItems($user),
        new ListBudgets($user),
        new ListProgressReports($user),
        new ListBillings($user),
        new ListMarketingCampaigns($user),
        new GetMarketingStatistics($user),
        new ListTeamMembers($user),
        new GetCompanyProfile($user),
        new GetDashboardSummary($user),
        new GetFinancialOverview($user),
        new GetExpenseBreakdown($user),
    ];

    foreach ($tools as $tool) {
        $result = json_decode((string) $tool->handle(new Request), true);

        expect($result['ok'])->toBeTrue(class_basename($tool).': '.json_encode($result));
    }
});

test('the cross-record search finds rows across domains', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    Client::factory()->create([
        'company_id' => $company->id,
        'name' => 'Northwind Traders',
    ]);

    $result = json_decode(
        (string) (new SearchRecords($user))->handle(new Request(['term' => 'Northwind'])),
        true,
    );

    expect($result['ok'])->toBeTrue()
        ->and($result['data']['clients'])->toHaveCount(1)
        ->and($result['data']['clients'][0]['label'])->toBe('Northwind Traders');
});
