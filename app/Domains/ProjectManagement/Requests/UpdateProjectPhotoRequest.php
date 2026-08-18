<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Requests;

use App\Domains\ProjectManagement\DTOs\ProjectPhotoData;
use App\Domains\ProjectManagement\Enums\PhotoCategory;
use Illuminate\Validation\Rule;

class UpdateProjectPhotoRequest extends StoreProjectPhotoRequest
{
    public function authorize(): bool
    {
        $photo = $this->route('projectPhoto');

        return $this->user()?->can('update', $photo) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'category' => ['required', Rule::enum(PhotoCategory::class)],
            'photo' => ['nullable', 'image', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toDTO(): ProjectPhotoData
    {
        $photo = $this->route('projectPhoto');

        return new ProjectPhotoData(
            projectId: (int) $this->validated('project_id'),
            category: PhotoCategory::from($this->validated('category')),
            filePath: $this->hasFile('photo')
                ? $this->file('photo')->store('project-photos')
                : $photo->file_path,
            caption: $this->validated('caption'),
        );
    }
}
