<?php

declare(strict_types=1);

namespace App\Domains\Sales\Actions;

use App\Domains\Finance\Actions\CreateBudgetAction;
use App\Domains\Finance\DTOs\BudgetData;
use App\Domains\Finance\Enums\BudgetStatus;
use App\Domains\Finance\Enums\BudgetType;
use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Models\ChangeOrder;
use App\Domains\Sales\Models\ChangeOrderItem;
use Illuminate\Support\Facades\DB;

final class DecideChangeOrderAction
{
    public function __construct(
        private readonly CreateBudgetAction $createBudgetAction,
    ) {}

    /**
     * Approve or reject a change order.
     */
    public function execute(ChangeOrder $changeOrder, ChangeOrderStatus $status): ChangeOrder
    {
        DB::transaction(function () use ($changeOrder, $status) {
            $changeOrder->forceFill([
                'status' => $status,
                'approved_at' => $status === ChangeOrderStatus::Approved ? now() : null,
            ])->save();

            if ($status === ChangeOrderStatus::Approved) {
                $this->reviseBudget($changeOrder);
            }
        });

        return $changeOrder;
    }

    /**
     * Add the approved change order value to the project budget.
     */
    private function reviseBudget(ChangeOrder $changeOrder): void
    {
        $changeOrder->loadMissing('items');

        $items = $changeOrder->items->map(fn (ChangeOrderItem $item) => [
            'description' => $item->description,
            'quantity' => 1,
            'unit' => 'lot',
            'unit_price' => $item->amount,
            'cost_code_id' => null,
            'boq_item_id' => null,
        ])->all();

        if ($items === []) {
            $items = [[
                'description' => 'Change order '.$changeOrder->co_number,
                'quantity' => 1,
                'unit' => 'lot',
                'unit_price' => $changeOrder->amount,
            ]];
        }

        $this->createBudgetAction->execute(new BudgetData(
            projectId: $changeOrder->project_id,
            type: BudgetType::Revised,
            status: BudgetStatus::Approved,
            items: $items,
        ), $changeOrder->company_id);
    }
}
