<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Finance\Enums\BudgetStatus;
use App\Domains\Finance\Enums\BudgetType;
use App\Domains\Finance\Models\Budget;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    public function definition(): array
    {
        return [
            'type' => BudgetType::Original,
            'version' => 1,
            'is_current' => true,
            'status' => fake()->randomElement(BudgetStatus::cases()),
            'currency' => 'PHP',
        ];
    }
}
