<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Finance\Enums\ContractStatus;
use App\Domains\Finance\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
{
    protected $model = Contract::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'contract_value_amount' => fake()->numberBetween(1_000_000, 50_000_000),
            'contract_value_currency' => 'PHP',
            'signed_at' => fake()->optional()->date(),
            'status' => fake()->randomElement(ContractStatus::cases()),
        ];
    }
}
