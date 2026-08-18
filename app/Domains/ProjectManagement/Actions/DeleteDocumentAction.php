<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Actions;

use App\Domains\ProjectManagement\Models\Document;
use Illuminate\Support\Facades\DB;

final class DeleteDocumentAction
{
    public function execute(Document $document): void
    {
        DB::transaction(function () use ($document) {
            $document->delete();
        });
    }
}
