<?php

declare(strict_types=1);

namespace App\Domains\Sales\Controllers;

use App\Domains\Sales\Actions\CreateInvoiceAction;
use App\Domains\Sales\Actions\DeleteInvoiceAction;
use App\Domains\Sales\Actions\UpdateInvoiceAction;
use App\Domains\Sales\Models\Invoice;
use App\Domains\Sales\Requests\StoreInvoiceRequest;
use App\Domains\Sales\Requests\UpdateInvoiceRequest;
use App\Domains\Sales\Resources\InvoiceResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class InvoiceController extends Controller
{
    public function __construct(
        private readonly CreateInvoiceAction $createInvoiceAction,
        private readonly UpdateInvoiceAction $updateInvoiceAction,
        private readonly DeleteInvoiceAction $deleteInvoiceAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $invoices = Invoice::with(['project', 'items'])
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->get();

        return InvoiceResource::collection($invoices)->response();
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->createInvoiceAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
        );

        return response()->json(new InvoiceResource($invoice), Response::HTTP_CREATED);
    }

    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);

        return response()->json(new InvoiceResource($invoice->load('items')));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        $invoice = $this->updateInvoiceAction->execute($invoice, $request->toDTO());

        return response()->json(new InvoiceResource($invoice));
    }

    public function destroy(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('delete', $invoice);

        $this->deleteInvoiceAction->execute($invoice);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
