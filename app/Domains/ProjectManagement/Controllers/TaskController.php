<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Controllers;

use App\Domains\ProjectManagement\Actions\CreateTaskAction;
use App\Domains\ProjectManagement\Actions\DeleteTaskAction;
use App\Domains\ProjectManagement\Actions\UpdateTaskAction;
use App\Domains\ProjectManagement\Models\Task;
use App\Domains\ProjectManagement\Requests\StoreTaskRequest;
use App\Domains\ProjectManagement\Requests\UpdateTaskRequest;
use App\Domains\ProjectManagement\Resources\TaskResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class TaskController extends Controller
{
    public function __construct(
        private readonly CreateTaskAction $createTaskAction,
        private readonly UpdateTaskAction $updateTaskAction,
        private readonly DeleteTaskAction $deleteTaskAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tasks = Task::with(['project', 'assignee'])
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->get();

        return TaskResource::collection($tasks)->response();
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->createTaskAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
        );

        return response()->json(new TaskResource($task), Response::HTTP_CREATED);
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        return response()->json(new TaskResource($task));
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $task = $this->updateTaskAction->execute($task, $request->toDTO());

        return response()->json(new TaskResource($task));
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $this->deleteTaskAction->execute($task);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
