<?php

declare(strict_types=1);

namespace App\Domains\Sales\Controllers;

use App\Domains\Sales\Actions\CreateLeadAction;
use App\Domains\Sales\Actions\DeleteLeadAction;
use App\Domains\Sales\Actions\UpdateLeadAction;
use App\Domains\Sales\Models\Lead;
use App\Domains\Sales\Requests\StoreLeadRequest;
use App\Domains\Sales\Requests\UpdateLeadRequest;
use App\Domains\Sales\Resources\LeadResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class LeadController extends Controller
{
    public function __construct(
        private readonly CreateLeadAction $createLeadAction,
        private readonly UpdateLeadAction $updateLeadAction,
        private readonly DeleteLeadAction $deleteLeadAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $leads = Lead::where('company_id', $request->user()->company_id)->latest()->get();

        return LeadResource::collection($leads)->response();
    }

    public function store(StoreLeadRequest $request): JsonResponse
    {
        $lead = $this->createLeadAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
        );

        return response()->json(new LeadResource($lead), Response::HTTP_CREATED);
    }

    public function show(Request $request, Lead $lead): JsonResponse
    {
        $this->authorize('view', $lead);

        return response()->json(new LeadResource($lead));
    }

    public function update(UpdateLeadRequest $request, Lead $lead): JsonResponse
    {
        $lead = $this->updateLeadAction->execute($lead, $request->toDTO());

        return response()->json(new LeadResource($lead));
    }

    public function destroy(Request $request, Lead $lead): JsonResponse
    {
        $this->authorize('delete', $lead);

        $this->deleteLeadAction->execute($lead);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
