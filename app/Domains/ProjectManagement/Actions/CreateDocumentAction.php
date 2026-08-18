<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\DTOs\DocumentData;
use App\Domains\ProjectManagement\Models\Document;
use Illuminate\Support\Facades\DB;

final class CreateDocumentAction
{
    public function execute(DocumentData $data, int $companyId): Document
    {
        return DB::transaction(function () use ($data, $companyId) {
            return Document::create([
                'company_id' => $companyId,
                'project_id' => $data->projectId,
                'name' => $data->name,
                'category' => $data->category,
                'file_path' => $data->filePath,
            ]);
        });
    }
}
