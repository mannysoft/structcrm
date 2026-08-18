<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Requests;

use App\Domains\ProjectManagement\DTOs\DocumentData;
use App\Domains\ProjectManagement\Enums\DocumentCategory;
use App\Domains\ProjectManagement\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Document::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(DocumentCategory::class)],
            'file' => ['required', 'file', 'max:10240'],
        ];
    }

    public function toDTO(): DocumentData
    {
        return new DocumentData(
            projectId: (int) $this->validated('project_id'),
            name: $this->validated('name'),
            category: DocumentCategory::from($this->validated('category')),
            filePath: $this->file('file')->store('documents'),
        );
    }
}
