<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Finance;

use App\Common\ValueObjects\Money;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Actions\CreateContractAction;
use App\Domains\Finance\DTOs\ContractData;
use App\Domains\Finance\Enums\ContractStatus;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;

test('it creates a contract scoped to a company', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    $contract = app(CreateContractAction::class)->execute(new ContractData(
        projectId: $project->id,
        title: 'Main Construction Contract',
        contractValue: Money::fromDecimal(20000000, 'PHP'),
        status: ContractStatus::Signed,
    ), $company->id);

    $this->assertSame('CON-1', $contract->contract_number);
    $this->assertSame('Main Construction Contract', $contract->title);
    $this->assertSame(2000000000, $contract->contract_value_amount);
    $this->assertSame('PHP', $contract->contract_value_currency);
    $this->assertNotNull($contract->uuid);

    $this->assertDatabaseHas('contracts', [
        'id' => $contract->id,
        'title' => 'Main Construction Contract',
    ]);
});
