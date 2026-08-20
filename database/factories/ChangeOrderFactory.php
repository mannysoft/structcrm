<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Models\ChangeOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChangeOrder>
 */
class ChangeOrderFactory extends Factory
{
    protected $model = ChangeOrder::class;

    public function definition(): array
    {
        return [
            'description' => $this->faker->sentence(),
            'amount' => $this->faker->numberBetween(-50000, 500000),
            'amount_currency' => 'PHP',
            'status' => $this->faker->randomElement(ChangeOrderStatus::cases()),
        ];
    }
}
