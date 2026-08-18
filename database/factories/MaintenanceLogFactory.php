<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\ProjectManagement\Models\MaintenanceLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceLog>
 */
class MaintenanceLogFactory extends Factory
{
    protected $model = MaintenanceLog::class;

    public function definition(): array
    {
        return [
            'service_date' => $this->faker->dateTimeThisYear()->format('Y-m-d'),
            'description' => $this->faker->sentence(),
            'cost_amount' => $this->faker->numberBetween(0, 100000),
            'cost_currency' => 'USD',
        ];
    }
}
