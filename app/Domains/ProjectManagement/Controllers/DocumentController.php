<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Controllers;

use App\Domains\ProjectManagement\Actions\CreateDocumentAction;
use App\Domains\ProjectManagement\Actions\DeleteDocumentAction;
use App\Domains\ProjectManagement\Actions\UpdateDocumentAction;
use App\Domains\ProjectManagement\Models\Document;
use App\Domains\ProjectManagement\Requests\StoreDocumentRequest;
use App\Domains\ProjectManagement\Requests\UpdateDocumentRequest;
use App\Domains\ProjectManagement\Resources\DocumentResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class DocumentController extends Controller
{
    public function __construct(
        private readonly CreateDocumentAction $createDocumentAction,
        private readonly UpdateDocumentAction $updateDocumentAction,
        private readonly DeleteDocumentAction $deleteDocumentAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $documents = Document::with('project')
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->get();

        return DocumentResource::collection($documents)->response();
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $document = $this->createDocumentAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
        );

        return response()->json(new DocumentResource($document), Response::HTTP_CREATED);
    }

    public function show(Request $request, Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        return response()->json(new DocumentResource($document));
    }

    public function update(UpdateDocumentRequest $request, Document $document): JsonResponse
    {
        $document = $this->updateDocumentAction->execute($document, $request->toDTO());

        return response()->json(new DocumentResource($document));
    }

    public function destroy(Request $request, Document $document): JsonResponse
    {
        $this->authorize('delete', $document);

        $this->deleteDocumentAction->execute($document);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
