<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Domains\ProjectManagement\Requests\StoreProjectRequest;
use App\Domains\ProjectManagement\Actions\CreateProjectAction;
use App\Domains\ProjectManagement\Resources\ProjectResource;
use Illuminate\Http\Response;

final class ProjectController extends Controller
{
    public function __construct(
        private readonly CreateProjectAction $createProjectAction
    ) {}

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->createProjectAction->execute(
            $request->toDTO(), 
            $request->user()->company_id
        );

        return response()->json(
            new ProjectResource($project), 
            Response::HTTP_CREATED
        );
    }
}
