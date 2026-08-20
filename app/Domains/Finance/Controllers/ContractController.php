<?php

declare(strict_types=1);

namespace App\Domains\Finance\Controllers;

use App\Domains\Finance\Actions\CreateContractAction;
use App\Domains\Finance\Actions\DeleteContractAction;
use App\Domains\Finance\Actions\UpdateContractAction;
use App\Domains\Finance\Models\Contract;
use App\Domains\Finance\Requests\StoreContractRequest;
use App\Domains\Finance\Requests\UpdateContractRequest;
use App\Domains\Finance\Resources\ContractResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ContractController extends Controller
{
    public function __construct(
        private readonly CreateContractAction $createContractAction,
        private readonly UpdateContractAction $updateContractAction,
        private readonly DeleteContractAction $deleteContractAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $contracts = Contract::with('project')
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->get();

        return ContractResource::collection($contracts)->response();
    }

    public function store(StoreContractRequest $request): JsonResponse
    {
        $contract = $this->createContractAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
        );

        return response()->json(new ContractResource($contract), Response::HTTP_CREATED);
    }

    public function show(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('view', $contract);

        return response()->json(new ContractResource($contract));
    }

    public function update(UpdateContractRequest $request, Contract $contract): JsonResponse
    {
        $contract = $this->updateContractAction->execute($contract, $request->toDTO());

        return response()->json(new ContractResource($contract));
    }

    public function destroy(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('delete', $contract);

        $this->deleteContractAction->execute($contract);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
