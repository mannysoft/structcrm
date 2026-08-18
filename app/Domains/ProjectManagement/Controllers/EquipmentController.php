<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Controllers;

use App\Domains\ProjectManagement\Actions\CreateEquipmentAction;
use App\Domains\ProjectManagement\Actions\DeleteEquipmentAction;
use App\Domains\ProjectManagement\Actions\UpdateEquipmentAction;
use App\Domains\ProjectManagement\Models\Equipment;
use App\Domains\ProjectManagement\Requests\StoreEquipmentRequest;
use App\Domains\ProjectManagement\Requests\UpdateEquipmentRequest;
use App\Domains\ProjectManagement\Resources\EquipmentResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class EquipmentController extends Controller
{
    public function __construct(
        private readonly CreateEquipmentAction $createEquipmentAction,
        private readonly UpdateEquipmentAction $updateEquipmentAction,
        private readonly DeleteEquipmentAction $deleteEquipmentAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $equipment = Equipment::withCount('maintenanceLogs')
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->get();

        return EquipmentResource::collection($equipment)->response();
    }

    public function store(StoreEquipmentRequest $request): JsonResponse
    {
        $equipment = $this->createEquipmentAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
        );

        return response()->json(new EquipmentResource($equipment), Response::HTTP_CREATED);
    }

    public function show(Request $request, Equipment $equipment): JsonResponse
    {
        $this->authorize('view', $equipment);

        return response()->json(new EquipmentResource($equipment));
    }

    public function update(UpdateEquipmentRequest $request, Equipment $equipment): JsonResponse
    {
        $equipment = $this->updateEquipmentAction->execute($equipment, $request->toDTO());

        return response()->json(new EquipmentResource($equipment));
    }

    public function destroy(Request $request, Equipment $equipment): JsonResponse
    {
        $this->authorize('delete', $equipment);

        $this->deleteEquipmentAction->execute($equipment);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
