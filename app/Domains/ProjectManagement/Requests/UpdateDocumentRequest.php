<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Requests;

use App\Domains\ProjectManagement\DTOs\DocumentData;
use App\Domains\ProjectManagement\Enums\DocumentCategory;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends StoreDocumentRequest
{
    public function authorize(): bool
    {
        $document = $this->route('document');

        return $this->user()?->can('update', $document) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(DocumentCategory::class)],
            'file' => ['nullable', 'file', 'max:10240'],
        ];
    }

    public function toDTO(): DocumentData
    {
        $document = $this->route('document');

        return new DocumentData(
            projectId: (int) $this->validated('project_id'),
            name: $this->validated('name'),
            category: DocumentCategory::from($this->validated('category')),
            filePath: $this->hasFile('file')
                ? $this->file('file')->store('documents')
                : $document->file_path,
        );
    }
}
