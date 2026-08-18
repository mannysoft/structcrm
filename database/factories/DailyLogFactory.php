<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\ProjectManagement\Models\DailyLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyLog>
 */
class DailyLogFactory extends Factory
{
    protected $model = DailyLog::class;

    public function definition(): array
    {
        return [
            'log_date' => $this->faker->dateTimeBetween('-1 week', 'now')->format('Y-m-d'),
            'weather' => $this->faker->randomElement(['Sunny', 'Cloudy', 'Rainy', 'Windy']),
            'crew_count' => $this->faker->numberBetween(1, 20),
            'labor_hours' => $this->faker->randomFloat(2, 1, 100),
            'work_performed' => $this->faker->sentence(),
            'issues' => $this->faker->sentence(),
        ];
    }
}
