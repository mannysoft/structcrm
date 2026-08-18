<?php

declare(strict_types=1);

namespace App\Domains\Sales\Controllers;

use App\Domains\Sales\Actions\CreateSupplierAction;
use App\Domains\Sales\Actions\DeleteSupplierAction;
use App\Domains\Sales\Actions\UpdateSupplierAction;
use App\Domains\Sales\Models\Supplier;
use App\Domains\Sales\Requests\StoreSupplierRequest;
use App\Domains\Sales\Requests\UpdateSupplierRequest;
use App\Domains\Sales\Resources\SupplierResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class SupplierController extends Controller
{
    public function __construct(
        private readonly CreateSupplierAction $createSupplierAction,
        private readonly UpdateSupplierAction $updateSupplierAction,
        private readonly DeleteSupplierAction $deleteSupplierAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $suppliers = Supplier::where('company_id', $request->user()->company_id)
            ->latest()
            ->get();

        return SupplierResource::collection($suppliers)->response();
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = $this->createSupplierAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
        );

        return response()->json(new SupplierResource($supplier), Response::HTTP_CREATED);
    }

    public function show(Request $request, Supplier $supplier): JsonResponse
    {
        $this->authorize('view', $supplier);

        return response()->json(new SupplierResource($supplier));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier = $this->updateSupplierAction->execute($supplier, $request->toDTO());

        return response()->json(new SupplierResource($supplier));
    }

    public function destroy(Request $request, Supplier $supplier): JsonResponse
    {
        $this->authorize('delete', $supplier);

        $this->deleteSupplierAction->execute($supplier);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
