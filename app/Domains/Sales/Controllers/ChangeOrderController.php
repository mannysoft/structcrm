<?php

declare(strict_types=1);

namespace App\Domains\Sales\Controllers;

use App\Domains\Sales\Actions\CreateChangeOrderAction;
use App\Domains\Sales\Actions\DeleteChangeOrderAction;
use App\Domains\Sales\Actions\UpdateChangeOrderAction;
use App\Domains\Sales\Models\ChangeOrder;
use App\Domains\Sales\Requests\StoreChangeOrderRequest;
use App\Domains\Sales\Requests\UpdateChangeOrderRequest;
use App\Domains\Sales\Resources\ChangeOrderResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ChangeOrderController extends Controller
{
    public function __construct(
        private readonly CreateChangeOrderAction $createChangeOrderAction,
        private readonly UpdateChangeOrderAction $updateChangeOrderAction,
        private readonly DeleteChangeOrderAction $deleteChangeOrderAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $changeOrders = ChangeOrder::with(['project', 'items'])
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->get();

        return ChangeOrderResource::collection($changeOrders)->response();
    }

    public function store(StoreChangeOrderRequest $request): JsonResponse
    {
        $changeOrder = $this->createChangeOrderAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
        );

        return response()->json(new ChangeOrderResource($changeOrder), Response::HTTP_CREATED);
    }

    public function show(Request $request, ChangeOrder $changeOrder): JsonResponse
    {
        $this->authorize('view', $changeOrder);

        return response()->json(new ChangeOrderResource($changeOrder));
    }

    public function update(UpdateChangeOrderRequest $request, ChangeOrder $changeOrder): JsonResponse
    {
        $changeOrder = $this->updateChangeOrderAction->execute($changeOrder, $request->toDTO());

        return response()->json(new ChangeOrderResource($changeOrder));
    }

    public function destroy(Request $request, ChangeOrder $changeOrder): JsonResponse
    {
        $this->authorize('delete', $changeOrder);

        $this->deleteChangeOrderAction->execute($changeOrder);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
