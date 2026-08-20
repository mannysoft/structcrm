<?php

declare(strict_types=1);

namespace App\Domains\Finance\Controllers;

use App\Domains\Finance\Actions\CreateBillingAction;
use App\Domains\Finance\Actions\DeleteBillingAction;
use App\Domains\Finance\Actions\RecordBillingPaymentAction;
use App\Domains\Finance\Actions\UpdateBillingAction;
use App\Domains\Finance\Models\Billing;
use App\Domains\Finance\Requests\StoreBillingPaymentRequest;
use App\Domains\Finance\Requests\StoreBillingRequest;
use App\Domains\Finance\Requests\UpdateBillingRequest;
use App\Domains\Finance\Resources\BillingResource;
use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class BillingController extends Controller
{
    public function __construct(
        private readonly CreateBillingAction $createBillingAction,
        private readonly UpdateBillingAction $updateBillingAction,
        private readonly DeleteBillingAction $deleteBillingAction,
        private readonly RecordBillingPaymentAction $recordBillingPaymentAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $billings = Billing::with(['project', 'items', 'payments'])
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->get();

        return BillingResource::collection($billings)->response();
    }

    public function store(StoreBillingRequest $request): JsonResponse
    {
        $billing = $this->createBillingAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
        );

        return response()->json(new BillingResource($billing), Response::HTTP_CREATED);
    }

    public function show(Request $request, Billing $billing): JsonResponse
    {
        $this->authorize('view', $billing);

        return response()->json(new BillingResource($billing->load(['items', 'payments'])));
    }

    public function update(UpdateBillingRequest $request, Billing $billing): JsonResponse
    {
        $billing = $this->updateBillingAction->execute($billing, $request->toDTO());

        return response()->json(new BillingResource($billing->load(['items', 'payments'])));
    }

    public function destroy(Request $request, Billing $billing): JsonResponse
    {
        $this->authorize('delete', $billing);

        $this->deleteBillingAction->execute($billing);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function storePayment(StoreBillingPaymentRequest $request, Billing $billing): JsonResponse
    {
        $billing = $this->recordBillingPaymentAction->execute(
            $billing,
            CarbonImmutable::parse($request->validated('payment_date')),
            (int) round((float) $request->validated('amount') * 100),
            $request->validated('method'),
            $request->validated('reference'),
            $request->validated('notes'),
        );

        return response()->json(new BillingResource($billing->load(['items', 'payments'])));
    }
}
