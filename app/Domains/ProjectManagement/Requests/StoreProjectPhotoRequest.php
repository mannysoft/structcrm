<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Requests;

use App\Domains\ProjectManagement\DTOs\ProjectPhotoData;
use App\Domains\ProjectManagement\Enums\PhotoCategory;
use App\Domains\ProjectManagement\Models\ProjectPhoto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ProjectPhoto::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'category' => ['required', Rule::enum(PhotoCategory::class)],
            'photo' => ['required', 'image', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toDTO(): ProjectPhotoData
    {
        return new ProjectPhotoData(
            projectId: (int) $this->validated('project_id'),
            category: PhotoCategory::from($this->validated('category')),
            filePath: $this->file('photo')->store('project-photos'),
            caption: $this->validated('caption'),
        );
    }
}
