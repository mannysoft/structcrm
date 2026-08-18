<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\ProjectManagement\Enums\DocumentCategory;
use App\Domains\ProjectManagement\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true).'.'.$this->faker->fileExtension(),
            'category' => $this->faker->randomElement(DocumentCategory::cases()),
            'file_path' => 'documents/'.$this->faker->uuid().'.pdf',
        ];
    }
}
