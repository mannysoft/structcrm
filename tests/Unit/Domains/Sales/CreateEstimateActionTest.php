<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Actions\CreateEstimateAction;
use App\Domains\Sales\DTOs\EstimateData;
use App\Domains\Sales\Enums\EstimateStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateEstimateActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_estimate_with_items_and_tax(): void
    {
        $company = Company::factory()->create();

        $estimate = app(CreateEstimateAction::class)->execute(new EstimateData(
            title: 'Kitchen remodel',
            status: EstimateStatus::Sent,
            items: [
                ['description' => 'Cabinets', 'quantity' => 1, 'unit_price' => 500000],
            ],
            taxRate: 10.0,
        ), $company->id);

        $this->assertSame('EST-1', $estimate->estimate_number);
        $this->assertSame(500000, $estimate->subtotal);
        $this->assertSame(50000, $estimate->tax_amount);
        $this->assertSame(550000, $estimate->amount);
        $this->assertCount(1, $estimate->items);

        $this->assertDatabaseHas('estimate_items', [
            'estimate_id' => $estimate->id,
            'description' => 'Cabinets',
        ]);
    }
}
