<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Finance\Enums\CostCodeType;
use App\Domains\Finance\Models\CostCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CostCode>
 */
class CostCodeFactory extends Factory
{
    protected $model = CostCode::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('CC-###'),
            'name' => fake()->unique()->words(2, true),
            'type' => fake()->randomElement(CostCodeType::cases()),
        ];
    }
}
