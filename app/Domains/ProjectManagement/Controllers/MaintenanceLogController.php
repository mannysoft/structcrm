<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Controllers;

use App\Domains\ProjectManagement\Actions\CreateMaintenanceLogAction;
use App\Domains\ProjectManagement\Actions\DeleteMaintenanceLogAction;
use App\Domains\ProjectManagement\Actions\UpdateMaintenanceLogAction;
use App\Domains\ProjectManagement\Models\MaintenanceLog;
use App\Domains\ProjectManagement\Requests\StoreMaintenanceLogRequest;
use App\Domains\ProjectManagement\Requests\UpdateMaintenanceLogRequest;
use App\Domains\ProjectManagement\Resources\MaintenanceLogResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class MaintenanceLogController extends Controller
{
    public function __construct(
        private readonly CreateMaintenanceLogAction $createMaintenanceLogAction,
        private readonly UpdateMaintenanceLogAction $updateMaintenanceLogAction,
        private readonly DeleteMaintenanceLogAction $deleteMaintenanceLogAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $logs = MaintenanceLog::with('equipment')
            ->where('company_id', $request->user()->company_id)
            ->latest('service_date')
            ->get();

        return MaintenanceLogResource::collection($logs)->response();
    }

    public function store(StoreMaintenanceLogRequest $request): JsonResponse
    {
        $log = $this->createMaintenanceLogAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
        );

        return response()->json(new MaintenanceLogResource($log), Response::HTTP_CREATED);
    }

    public function show(Request $request, MaintenanceLog $maintenanceLog): JsonResponse
    {
        $this->authorize('view', $maintenanceLog);

        return response()->json(new MaintenanceLogResource($maintenanceLog));
    }

    public function update(UpdateMaintenanceLogRequest $request, MaintenanceLog $maintenanceLog): JsonResponse
    {
        $maintenanceLog = $this->updateMaintenanceLogAction->execute($maintenanceLog, $request->toDTO());

        return response()->json(new MaintenanceLogResource($maintenanceLog));
    }

    public function destroy(Request $request, MaintenanceLog $maintenanceLog): JsonResponse
    {
        $this->authorize('delete', $maintenanceLog);

        $this->deleteMaintenanceLogAction->execute($maintenanceLog);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
