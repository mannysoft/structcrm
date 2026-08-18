<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Controllers;

use App\Domains\ProjectManagement\Actions\CreateProjectPhotoAction;
use App\Domains\ProjectManagement\Actions\DeleteProjectPhotoAction;
use App\Domains\ProjectManagement\Actions\UpdateProjectPhotoAction;
use App\Domains\ProjectManagement\Models\ProjectPhoto;
use App\Domains\ProjectManagement\Requests\StoreProjectPhotoRequest;
use App\Domains\ProjectManagement\Requests\UpdateProjectPhotoRequest;
use App\Domains\ProjectManagement\Resources\ProjectPhotoResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ProjectPhotoController extends Controller
{
    public function __construct(
        private readonly CreateProjectPhotoAction $createProjectPhotoAction,
        private readonly UpdateProjectPhotoAction $updateProjectPhotoAction,
        private readonly DeleteProjectPhotoAction $deleteProjectPhotoAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $photos = ProjectPhoto::with('project')
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->get();

        return ProjectPhotoResource::collection($photos)->response();
    }

    public function store(StoreProjectPhotoRequest $request): JsonResponse
    {
        $photo = $this->createProjectPhotoAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
            $request->user()->id,
        );

        return response()->json(new ProjectPhotoResource($photo), Response::HTTP_CREATED);
    }

    public function show(Request $request, ProjectPhoto $photo): JsonResponse
    {
        $this->authorize('view', $photo);

        return response()->json(new ProjectPhotoResource($photo));
    }

    public function update(UpdateProjectPhotoRequest $request, ProjectPhoto $photo): JsonResponse
    {
        $dto = $request->toDTO();

        $photo = $this->updateProjectPhotoAction->execute($photo, $dto);

        return response()->json(new ProjectPhotoResource($photo));
    }

    public function destroy(Request $request, ProjectPhoto $photo): JsonResponse
    {
        $this->authorize('delete', $photo);

        $this->deleteProjectPhotoAction->execute($photo);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
