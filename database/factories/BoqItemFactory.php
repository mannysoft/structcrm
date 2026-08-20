<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Finance\Models\BoqItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BoqItem>
 */
class BoqItemFactory extends Factory
{
    protected $model = BoqItem::class;

    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 100);
        $unitPrice = fake()->numberBetween(100, 500000);

        return [
            'item_code' => fake()->optional()->numerify('BOQ-###'),
            'description' => fake()->sentence(3),
            'quantity' => $quantity,
            'unit' => fake()->randomElement(['cu.m.', 'sq.m.', 'm', 'lot', 'pcs']),
            'unit_price' => $unitPrice,
            'total' => (int) round($quantity * $unitPrice),
            'currency' => 'PHP',
        ];
    }
}
