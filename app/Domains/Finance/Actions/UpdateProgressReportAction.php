<?php

declare(strict_types=1);

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\DTOs\ProgressReportData;
use App\Domains\Finance\Models\ProgressReport;
use Illuminate\Support\Facades\DB;

final class UpdateProgressReportAction
{
    public function execute(ProgressReport $progressReport, ProgressReportData $data): ProgressReport
    {
        return DB::transaction(function () use ($progressReport, $data) {
            $progressReport->update([
                'project_id' => $data->projectId,
                'report_date' => $data->reportDate,
                'status' => $data->status,
                'notes' => $data->notes,
            ]);

            $progressReport->items()->delete();

            foreach ($data->items as $item) {
                $progressReport->items()->create([
                    'boq_item_id' => $item['boq_item_id'] ?? null,
                    'cost_code_id' => $item['cost_code_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'percent_complete' => $item['percent_complete'] ?? 0,
                    'accomplished_amount' => $item['accomplished_amount'],
                ]);
            }

            return $progressReport->load('items');
        });
    }
}
