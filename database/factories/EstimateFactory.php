<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Sales\Enums\EstimateStatus;
use App\Domains\Sales\Models\Estimate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Estimate>
 */
class EstimateFactory extends Factory
{
    protected $model = Estimate::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'status' => $this->faker->randomElement(EstimateStatus::cases()),
            'subtotal' => 0,
            'tax_amount' => 0,
            'amount' => 0,
            'currency' => 'USD',
            'valid_until' => $this->faker->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
        ];
    }
}
