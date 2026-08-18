<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Sales\Enums\ExpenseCategory;
use App\Domains\Sales\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'category' => $this->faker->randomElement(ExpenseCategory::cases()),
            'amount' => $this->faker->numberBetween(1000, 100000),
            'currency' => 'USD',
            'date' => $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'notes' => $this->faker->sentence(),
        ];
    }
}
