<?php

declare(strict_types=1);

namespace App\Domains\Sales\Controllers;

use App\Domains\Sales\Actions\CreateEstimateAction;
use App\Domains\Sales\Actions\DeleteEstimateAction;
use App\Domains\Sales\Actions\UpdateEstimateAction;
use App\Domains\Sales\Models\Estimate;
use App\Domains\Sales\Requests\StoreEstimateRequest;
use App\Domains\Sales\Requests\UpdateEstimateRequest;
use App\Domains\Sales\Resources\EstimateResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class EstimateController extends Controller
{
    public function __construct(
        private readonly CreateEstimateAction $createEstimateAction,
        private readonly UpdateEstimateAction $updateEstimateAction,
        private readonly DeleteEstimateAction $deleteEstimateAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $estimates = Estimate::with('items')
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->get();

        return EstimateResource::collection($estimates)->response();
    }

    public function store(StoreEstimateRequest $request): JsonResponse
    {
        $estimate = $this->createEstimateAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
        );

        return response()->json(new EstimateResource($estimate), Response::HTTP_CREATED);
    }

    public function show(Request $request, Estimate $estimate): JsonResponse
    {
        $this->authorize('view', $estimate);

        return response()->json(new EstimateResource($estimate->load('items')));
    }

    public function update(UpdateEstimateRequest $request, Estimate $estimate): JsonResponse
    {
        $estimate = $this->updateEstimateAction->execute($estimate, $request->toDTO());

        return response()->json(new EstimateResource($estimate));
    }

    public function destroy(Request $request, Estimate $estimate): JsonResponse
    {
        $this->authorize('delete', $estimate);

        $this->deleteEstimateAction->execute($estimate);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
