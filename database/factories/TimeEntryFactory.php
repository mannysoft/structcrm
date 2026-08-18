<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\ProjectManagement\Models\TimeEntry;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeEntry>
 */
class TimeEntryFactory extends Factory
{
    protected $model = TimeEntry::class;

    public function definition(): array
    {
        $clockIn = CarbonImmutable::parse($this->faker->dateTimeThisMonth());

        return [
            'clock_in_at' => $clockIn,
            'clock_out_at' => $clockIn->addHours($this->faker->numberBetween(1, 8)),
            'break_minutes' => $this->faker->numberBetween(0, 60),
            'notes' => $this->faker->optional()->sentence(),
            'latitude' => $this->faker->optional()->latitude(),
            'longitude' => $this->faker->optional()->longitude(),
        ];
    }
}
