<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Finance;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Actions\CreateCostCodeAction;
use App\Domains\Finance\DTOs\CostCodeData;
use App\Domains\Finance\Enums\CostCodeType;

test('it creates a cost code scoped to a company', function () {

    $company = Company::factory()->create();

    $costCode = app(CreateCostCodeAction::class)->execute(new CostCodeData(
        code: '100',
        name: 'Materials',
        type: CostCodeType::Material,
    ), $company->id);

    $this->assertSame('100', $costCode->code);
    $this->assertSame('Materials', $costCode->name);
    $this->assertSame(CostCodeType::Material, $costCode->type);
    $this->assertNotNull($costCode->uuid);

    $this->assertDatabaseHas('cost_codes', [
        'id' => $costCode->id,
        'code' => '100',
    ]);
});
