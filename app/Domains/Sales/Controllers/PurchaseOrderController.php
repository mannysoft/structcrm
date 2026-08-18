<?php

declare(strict_types=1);

namespace App\Domains\Sales\Controllers;

use App\Domains\Sales\Actions\CreatePurchaseOrderAction;
use App\Domains\Sales\Actions\DeletePurchaseOrderAction;
use App\Domains\Sales\Actions\UpdatePurchaseOrderAction;
use App\Domains\Sales\Models\PurchaseOrder;
use App\Domains\Sales\Requests\StorePurchaseOrderRequest;
use App\Domains\Sales\Requests\UpdatePurchaseOrderRequest;
use App\Domains\Sales\Resources\PurchaseOrderResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class PurchaseOrderController extends Controller
{
    public function __construct(
        private readonly CreatePurchaseOrderAction $createPurchaseOrderAction,
        private readonly UpdatePurchaseOrderAction $updatePurchaseOrderAction,
        private readonly DeletePurchaseOrderAction $deletePurchaseOrderAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $purchaseOrders = PurchaseOrder::with(['project', 'supplier', 'items'])
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->get();

        return PurchaseOrderResource::collection($purchaseOrders)->response();
    }

    public function store(StorePurchaseOrderRequest $request): JsonResponse
    {
        $purchaseOrder = $this->createPurchaseOrderAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
        );

        return response()->json(new PurchaseOrderResource($purchaseOrder), Response::HTTP_CREATED);
    }

    public function show(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('view', $purchaseOrder);

        return response()->json(new PurchaseOrderResource($purchaseOrder->load('items')));
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder = $this->updatePurchaseOrderAction->execute($purchaseOrder, $request->toDTO());

        return response()->json(new PurchaseOrderResource($purchaseOrder));
    }

    public function destroy(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('delete', $purchaseOrder);

        $this->deletePurchaseOrderAction->execute($purchaseOrder);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
