<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Sales\Enums\PurchaseOrderStatus;
use App\Domains\Sales\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'status' => $this->faker->randomElement(PurchaseOrderStatus::cases()),
            'total' => 0,
        ];
    }
}
