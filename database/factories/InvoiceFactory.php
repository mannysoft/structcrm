<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Sales\Enums\InvoiceStatus;
use App\Domains\Sales\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'status' => $this->faker->randomElement(InvoiceStatus::cases()),
            'total' => 0,
            'issue_date' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'due_date' => $this->faker->dateTimeBetween('now', '+2 months')->format('Y-m-d'),
        ];
    }
}
