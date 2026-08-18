<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\DTOs\DocumentData;
use App\Domains\ProjectManagement\Models\Document;
use Illuminate\Support\Facades\DB;

final class UpdateDocumentAction
{
    public function execute(Document $document, DocumentData $data): Document
    {
        return DB::transaction(function () use ($document, $data) {
            $document->update([
                'project_id' => $data->projectId,
                'name' => $data->name,
                'category' => $data->category,
                'file_path' => $data->filePath,
            ]);

            return $document;
        });
    }
}
