<?php

declare(strict_types=1);

namespace App\Domains\Finance\Controllers;

use App\Domains\Finance\Actions\CreateBudgetAction;
use App\Domains\Finance\Actions\DeleteBudgetAction;
use App\Domains\Finance\Actions\UpdateBudgetAction;
use App\Domains\Finance\Models\Budget;
use App\Domains\Finance\Requests\StoreBudgetRequest;
use App\Domains\Finance\Requests\UpdateBudgetRequest;
use App\Domains\Finance\Resources\BudgetResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class BudgetController extends Controller
{
    public function __construct(
        private readonly CreateBudgetAction $createBudgetAction,
        private readonly UpdateBudgetAction $updateBudgetAction,
        private readonly DeleteBudgetAction $deleteBudgetAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $budgets = Budget::with(['project', 'items'])
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->get();

        return BudgetResource::collection($budgets)->response();
    }

    public function store(StoreBudgetRequest $request): JsonResponse
    {
        $budget = $this->createBudgetAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
        );

        return response()->json(new BudgetResource($budget), Response::HTTP_CREATED);
    }

    public function show(Request $request, Budget $budget): JsonResponse
    {
        $this->authorize('view', $budget);

        return response()->json(new BudgetResource($budget->load('items')));
    }

    public function update(UpdateBudgetRequest $request, Budget $budget): JsonResponse
    {
        $budget = $this->updateBudgetAction->execute($budget, $request->toDTO());

        return response()->json(new BudgetResource($budget));
    }

    public function destroy(Request $request, Budget $budget): JsonResponse
    {
        $this->authorize('delete', $budget);

        $this->deleteBudgetAction->execute($budget);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
