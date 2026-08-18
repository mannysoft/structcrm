<?php

declare(strict_types=1);

namespace App\Domains\Sales\Controllers;

use App\Domains\Sales\Actions\CreateExpenseAction;
use App\Domains\Sales\Actions\DeleteExpenseAction;
use App\Domains\Sales\Actions\UpdateExpenseAction;
use App\Domains\Sales\Models\Expense;
use App\Domains\Sales\Requests\StoreExpenseRequest;
use App\Domains\Sales\Requests\UpdateExpenseRequest;
use App\Domains\Sales\Resources\ExpenseResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ExpenseController extends Controller
{
    public function __construct(
        private readonly CreateExpenseAction $createExpenseAction,
        private readonly UpdateExpenseAction $updateExpenseAction,
        private readonly DeleteExpenseAction $deleteExpenseAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $expenses = Expense::with('project')
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->get();

        return ExpenseResource::collection($expenses)->response();
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $expense = $this->createExpenseAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
        );

        return response()->json(new ExpenseResource($expense), Response::HTTP_CREATED);
    }

    public function show(Request $request, Expense $expense): JsonResponse
    {
        $this->authorize('view', $expense);

        return response()->json(new ExpenseResource($expense));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
    {
        $expense = $this->updateExpenseAction->execute($expense, $request->toDTO());

        return response()->json(new ExpenseResource($expense));
    }

    public function destroy(Request $request, Expense $expense): JsonResponse
    {
        $this->authorize('delete', $expense);

        $this->deleteExpenseAction->execute($expense);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
