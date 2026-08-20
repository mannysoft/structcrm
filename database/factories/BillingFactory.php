<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Finance\Enums\BillingStatus;
use App\Domains\Finance\Models\Billing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Billing>
 */
class BillingFactory extends Factory
{
    protected $model = Billing::class;

    public function definition(): array
    {
        $gross = fake()->numberBetween(100_000, 5_000_000);
        $retention = (int) round($gross * 0.10);

        return [
            'period_start' => fake()->dateTimeBetween('-2 months', '-1 month')->format('Y-m-d'),
            'period_end' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'previous_billing_amount' => 0,
            'current_accomplishment_amount' => $gross,
            'gross_amount' => $gross,
            'retention_rate' => 0.10,
            'retention_amount' => $retention,
            'other_deductions_amount' => 0,
            'net_amount' => $gross - $retention,
            'currency' => 'PHP',
            'status' => fake()->randomElement(BillingStatus::cases()),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
        ];
    }
}
