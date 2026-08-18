<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\ProjectManagement\Enums\EquipmentStatus;
use App\Domains\ProjectManagement\Enums\EquipmentType;
use App\Domains\ProjectManagement\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    protected $model = Equipment::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'type' => $this->faker->randomElement(EquipmentType::cases()),
            'status' => $this->faker->randomElement(EquipmentStatus::cases()),
            'service_due_at' => $this->faker->optional()->dateTimeBetween('now', '+1 year')?->format('Y-m-d'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
