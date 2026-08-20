<?php

declare(strict_types=1);

namespace App\Domains\Finance\Controllers;

use App\Domains\Finance\Actions\CreateCostCodeAction;
use App\Domains\Finance\Actions\DeleteCostCodeAction;
use App\Domains\Finance\Actions\UpdateCostCodeAction;
use App\Domains\Finance\Models\CostCode;
use App\Domains\Finance\Requests\StoreCostCodeRequest;
use App\Domains\Finance\Requests\UpdateCostCodeRequest;
use App\Domains\Finance\Resources\CostCodeResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class CostCodeController extends Controller
{
    public function __construct(
        private readonly CreateCostCodeAction $createCostCodeAction,
        private readonly UpdateCostCodeAction $updateCostCodeAction,
        private readonly DeleteCostCodeAction $deleteCostCodeAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $costCodes = CostCode::with('parent')
            ->where('company_id', $request->user()->company_id)
            ->orderBy('code')
            ->get();

        return CostCodeResource::collection($costCodes)->response();
    }

    public function store(StoreCostCodeRequest $request): JsonResponse
    {
        $costCode = $this->createCostCodeAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
        );

        return response()->json(new CostCodeResource($costCode), Response::HTTP_CREATED);
    }

    public function show(Request $request, CostCode $costCode): JsonResponse
    {
        $this->authorize('view', $costCode);

        return response()->json(new CostCodeResource($costCode));
    }

    public function update(UpdateCostCodeRequest $request, CostCode $costCode): JsonResponse
    {
        $costCode = $this->updateCostCodeAction->execute($costCode, $request->toDTO());

        return response()->json(new CostCodeResource($costCode));
    }

    public function destroy(Request $request, CostCode $costCode): JsonResponse
    {
        $this->authorize('delete', $costCode);

        $this->deleteCostCodeAction->execute($costCode);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
