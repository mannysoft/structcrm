<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Finance\Enums\ProgressReportStatus;
use App\Domains\Finance\Models\ProgressReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgressReport>
 */
class ProgressReportFactory extends Factory
{
    protected $model = ProgressReport::class;

    public function definition(): array
    {
        return [
            'report_date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'status' => fake()->randomElement(ProgressReportStatus::cases()),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
