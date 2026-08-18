<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Controllers;

use App\Domains\ProjectManagement\Actions\CreateDailyLogAction;
use App\Domains\ProjectManagement\Actions\DeleteDailyLogAction;
use App\Domains\ProjectManagement\Actions\UpdateDailyLogAction;
use App\Domains\ProjectManagement\Models\DailyLog;
use App\Domains\ProjectManagement\Requests\StoreDailyLogRequest;
use App\Domains\ProjectManagement\Requests\UpdateDailyLogRequest;
use App\Domains\ProjectManagement\Resources\DailyLogResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class DailyLogController extends Controller
{
    public function __construct(
        private readonly CreateDailyLogAction $createDailyLogAction,
        private readonly UpdateDailyLogAction $updateDailyLogAction,
        private readonly DeleteDailyLogAction $deleteDailyLogAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $dailyLogs = DailyLog::with(['project', 'author'])
            ->where('company_id', $request->user()->company_id)
            ->latest('log_date')
            ->get();

        return DailyLogResource::collection($dailyLogs)->response();
    }

    public function store(StoreDailyLogRequest $request): JsonResponse
    {
        $dailyLog = $this->createDailyLogAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
            $request->user()->id,
        );

        return response()->json(new DailyLogResource($dailyLog), Response::HTTP_CREATED);
    }

    public function show(Request $request, DailyLog $dailyLog): JsonResponse
    {
        $this->authorize('view', $dailyLog);

        return response()->json(new DailyLogResource($dailyLog));
    }

    public function update(UpdateDailyLogRequest $request, DailyLog $dailyLog): JsonResponse
    {
        $dailyLog = $this->updateDailyLogAction->execute($dailyLog, $request->toDTO());

        return response()->json(new DailyLogResource($dailyLog));
    }

    public function destroy(Request $request, DailyLog $dailyLog): JsonResponse
    {
        $this->authorize('delete', $dailyLog);

        $this->deleteDailyLogAction->execute($dailyLog);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
