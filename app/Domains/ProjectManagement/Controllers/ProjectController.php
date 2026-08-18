<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Controllers;

use App\Domains\ProjectManagement\Actions\CreateProjectAction;
use App\Domains\ProjectManagement\Actions\DeleteProjectAction;
use App\Domains\ProjectManagement\Actions\UpdateProjectAction;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Requests\StoreProjectRequest;
use App\Domains\ProjectManagement\Requests\UpdateProjectRequest;
use App\Domains\ProjectManagement\Resources\ProjectResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ProjectController extends Controller
{
    public function __construct(
        private readonly CreateProjectAction $createProjectAction,
        private readonly UpdateProjectAction $updateProjectAction,
        private readonly DeleteProjectAction $deleteProjectAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $projects = Project::with('client')
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->get();

        return ProjectResource::collection($projects)->response();
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->createProjectAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
        );

        return response()->json(new ProjectResource($project), Response::HTTP_CREATED);
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return response()->json(new ProjectResource($project));
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $project = $this->updateProjectAction->execute($project, $request->toDTO());

        return response()->json(new ProjectResource($project));
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $this->deleteProjectAction->execute($project);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
