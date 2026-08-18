<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\ProjectManagement\Enums\PhotoCategory;
use App\Domains\ProjectManagement\Models\ProjectPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectPhoto>
 */
class ProjectPhotoFactory extends Factory
{
    protected $model = ProjectPhoto::class;

    public function definition(): array
    {
        return [
            'category' => $this->faker->randomElement(PhotoCategory::cases()),
            'file_path' => 'project-photos/'.$this->faker->uuid().'.jpg',
            'caption' => $this->faker->sentence(),
        ];
    }
}
