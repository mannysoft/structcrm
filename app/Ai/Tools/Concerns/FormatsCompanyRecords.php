<?php

declare(strict_types=1);

namespace App\Ai\Tools\Concerns;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Models\Billing;
use App\Domains\Finance\Models\BoqItem;
use App\Domains\Finance\Models\Budget;
use App\Domains\Finance\Models\Contract;
use App\Domains\Finance\Models\CostCode;
use App\Domains\Finance\Models\ProgressReport;
use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\ProjectManagement\Models\DailyLog;
use App\Domains\ProjectManagement\Models\Document;
use App\Domains\ProjectManagement\Models\Equipment;
use App\Domains\ProjectManagement\Models\MaintenanceLog;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\ProjectPhoto;
use App\Domains\ProjectManagement\Models\Task;
use App\Domains\ProjectManagement\Models\TimeEntry;
use App\Domains\Sales\Models\ChangeOrder;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Estimate;
use App\Domains\Sales\Models\Expense;
use App\Domains\Sales\Models\Invoice;
use App\Domains\Sales\Models\Lead;
use App\Domains\Sales\Models\PurchaseOrder;
use App\Domains\Sales\Models\Supplier;
use App\Models\User;

/**
 * Compact JSON representations shared by the assistant tools.
 */
trait FormatsCompanyRecords
{
    /**
     * @return array<string, mixed>
     */
    protected function companySummary(Company $company): array
    {
        return [
            'uuid' => $company->uuid,
            'name' => $company->name,
            'slug' => $company->slug,
            'email' => $company->email,
            'phone' => $company->phone,
            'address' => $company->address,
            'tax_number' => $company->tax_number,
            'timezone' => $company->timezone,
            'currency' => $this->enumValue($company->currency),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function teamMemberSummary(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'type' => $user->type,
            'roles' => $user->getRoleNames()->all(),
            'joined_at' => $user->created_at?->toDateString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function clientSummary(Client $client): array
    {
        return [
            'uuid' => $client->uuid,
            'name' => $client->name,
            'company_name' => $client->company_name,
            'email' => $client->email,
            'phone' => $client->phone,
            'address' => $client->address,
            'notes' => $client->notes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function leadSummary(Lead $lead): array
    {
        return [
            'uuid' => $lead->uuid,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'full_name' => trim($lead->first_name.' '.$lead->last_name),
            'email' => $lead->email,
            'phone' => $lead->phone,
            'status' => $this->enumValue($lead->status),
            'notes' => $lead->notes,
            'created_at' => $lead->created_at?->toDateString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function projectSummary(Project $project): array
    {
        return [
            'uuid' => $project->uuid,
            'project_number' => $project->project_number,
            'title' => $project->title,
            'status' => $this->enumValue($project->status),
            'client' => $project->client?->name,
            'client_uuid' => $project->client?->uuid,
            'manager' => $project->manager?->name,
            'budget' => $this->toDecimal($project->budget_amount),
            'budget_currency' => $project->budget_currency,
            'start_date' => $project->start_date?->toDateString(),
            'end_date' => $project->end_date?->toDateString(),
            'description' => $project->description,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function taskSummary(Task $task): array
    {
        return [
            'uuid' => $task->uuid,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $this->enumValue($task->status),
            'priority' => $this->enumValue($task->priority),
            'project' => $task->project?->title,
            'project_uuid' => $task->project?->uuid,
            'assignee' => $task->assignee?->name,
            'assigned_user_id' => $task->assigned_user_id,
            'due_date' => $task->due_date?->toDateString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function dailyLogSummary(DailyLog $log): array
    {
        return [
            'uuid' => $log->uuid,
            'log_date' => $log->log_date?->toDateString(),
            'project' => $log->project?->title,
            'project_uuid' => $log->project?->uuid,
            'author' => $log->author?->name,
            'weather' => $log->weather,
            'crew_count' => $log->crew_count,
            'labor_hours' => (float) $log->labor_hours,
            'equipment_usage' => $log->equipment_usage,
            'work_performed' => $log->work_performed,
            'issues' => $log->issues,
            'safety_incidents' => $log->safety_incidents,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function timeEntrySummary(TimeEntry $entry): array
    {
        return [
            'uuid' => $entry->uuid,
            'project' => $entry->project?->title,
            'project_uuid' => $entry->project?->uuid,
            'user' => $entry->user?->name,
            'clock_in_at' => $entry->clock_in_at?->toDateTimeString(),
            'clock_out_at' => $entry->clock_out_at?->toDateTimeString(),
            'break_minutes' => $entry->break_minutes,
            'notes' => $entry->notes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function documentSummary(Document $document): array
    {
        return [
            'uuid' => $document->uuid,
            'name' => $document->name,
            'category' => $this->enumValue($document->category),
            'file_path' => $document->file_path,
            'project' => $document->project?->title,
            'project_uuid' => $document->project?->uuid,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function photoSummary(ProjectPhoto $photo): array
    {
        return [
            'uuid' => $photo->uuid,
            'category' => $this->enumValue($photo->category),
            'caption' => $photo->caption,
            'file_path' => $photo->file_path,
            'project' => $photo->project?->title,
            'project_uuid' => $photo->project?->uuid,
            'uploaded_by' => $photo->uploader?->name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function equipmentSummary(Equipment $equipment): array
    {
        return [
            'uuid' => $equipment->uuid,
            'name' => $equipment->name,
            'type' => $this->enumValue($equipment->type),
            'status' => $this->enumValue($equipment->status),
            'service_due_at' => $equipment->service_due_at?->toDateString(),
            'notes' => $equipment->notes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function maintenanceLogSummary(MaintenanceLog $log): array
    {
        return [
            'uuid' => $log->uuid,
            'equipment' => $log->equipment?->name,
            'equipment_uuid' => $log->equipment?->uuid,
            'service_date' => $log->service_date?->toDateString(),
            'description' => $log->description,
            'cost' => $this->toDecimal($log->cost_amount),
            'cost_currency' => $log->cost_currency,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function estimateSummary(Estimate $estimate): array
    {
        return [
            'uuid' => $estimate->uuid,
            'estimate_number' => $estimate->estimate_number,
            'title' => $estimate->title,
            'status' => $this->enumValue($estimate->status),
            'amount' => $this->toDecimal($estimate->amount),
            'currency' => $estimate->currency,
            'valid_until' => $estimate->valid_until?->toDateString(),
            'lead_id' => $estimate->lead_id,
            'project_id' => $estimate->project_id,
            'items_count' => $estimate->items_count ?? $estimate->items()->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function invoiceSummary(Invoice $invoice): array
    {
        return [
            'uuid' => $invoice->uuid,
            'invoice_number' => $invoice->invoice_number,
            'status' => $this->enumValue($invoice->status),
            'total' => $this->toDecimal($invoice->total),
            'issue_date' => $invoice->issue_date?->toDateString(),
            'due_date' => $invoice->due_date?->toDateString(),
            'project_id' => $invoice->project_id,
            'items_count' => $invoice->items_count ?? $invoice->items()->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function changeOrderSummary(ChangeOrder $changeOrder): array
    {
        return [
            'uuid' => $changeOrder->uuid,
            'co_number' => $changeOrder->co_number,
            'description' => $changeOrder->description,
            'amount' => $this->toDecimal($changeOrder->amount),
            'currency' => $changeOrder->amount_currency,
            'status' => $this->enumValue($changeOrder->status),
            'approved_at' => $changeOrder->approved_at?->toDateTimeString(),
            'project_id' => $changeOrder->project_id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function purchaseOrderSummary(PurchaseOrder $purchaseOrder): array
    {
        return [
            'uuid' => $purchaseOrder->uuid,
            'po_number' => $purchaseOrder->po_number,
            'status' => $this->enumValue($purchaseOrder->status),
            'total' => $this->toDecimal($purchaseOrder->total),
            'supplier' => $purchaseOrder->supplier?->name,
            'supplier_uuid' => $purchaseOrder->supplier?->uuid,
            'project_id' => $purchaseOrder->project_id,
            'items_count' => $purchaseOrder->items_count ?? $purchaseOrder->items()->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function supplierSummary(Supplier $supplier): array
    {
        return [
            'uuid' => $supplier->uuid,
            'name' => $supplier->name,
            'email' => $supplier->email,
            'phone' => $supplier->phone,
            'address' => $supplier->address,
            'notes' => $supplier->notes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function expenseSummary(Expense $expense): array
    {
        return [
            'uuid' => $expense->uuid,
            'category' => $this->enumValue($expense->category),
            'amount' => $this->toDecimal($expense->amount),
            'currency' => $expense->currency,
            'date' => $expense->date?->toDateString(),
            'project_id' => $expense->project_id,
            'notes' => $expense->notes,
            'receipt_path' => $expense->receipt_path,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function costCodeSummary(CostCode $costCode): array
    {
        return [
            'uuid' => $costCode->uuid,
            'code' => $costCode->code,
            'name' => $costCode->name,
            'type' => $this->enumValue($costCode->type),
            'parent' => $costCode->parent?->name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function contractSummary(Contract $contract): array
    {
        return [
            'uuid' => $contract->uuid,
            'contract_number' => $contract->contract_number,
            'title' => $contract->title,
            'contract_value' => $this->toDecimal($contract->contract_value_amount),
            'currency' => $contract->contract_value_currency,
            'status' => $this->enumValue($contract->status),
            'signed_at' => $contract->signed_at?->toDateString(),
            'project_id' => $contract->project_id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function boqItemSummary(BoqItem $item): array
    {
        return [
            'uuid' => $item->uuid,
            'item_code' => $item->item_code,
            'description' => $item->description,
            'quantity' => (float) $item->quantity,
            'unit' => $item->unit,
            'unit_price' => $this->toDecimal($item->unit_price),
            'total' => $this->toDecimal($item->total),
            'currency' => $item->currency,
            'cost_code' => $item->costCode?->name,
            'cost_code_uuid' => $item->costCode?->uuid,
            'project_id' => $item->project_id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function budgetSummary(Budget $budget): array
    {
        return [
            'uuid' => $budget->uuid,
            'type' => $this->enumValue($budget->type),
            'version' => $budget->version,
            'is_current' => (bool) $budget->is_current,
            'status' => $this->enumValue($budget->status),
            'currency' => $budget->currency,
            'forecast' => $this->toDecimal($budget->forecast_amount),
            'total' => $this->toDecimal($budget->totalAmount()),
            'items_count' => $budget->items->count(),
            'project_id' => $budget->project_id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function progressReportSummary(ProgressReport $report): array
    {
        return [
            'uuid' => $report->uuid,
            'report_number' => $report->report_number,
            'report_date' => $report->report_date?->toDateString(),
            'status' => $this->enumValue($report->status),
            'notes' => $report->notes,
            'accomplished' => $this->toDecimal($report->totalAccomplished()),
            'items_count' => $report->items->count(),
            'project_id' => $report->project_id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function billingSummary(Billing $billing): array
    {
        $billing->loadMissing('payments');

        return [
            'uuid' => $billing->uuid,
            'billing_number' => $billing->billing_number,
            'status' => $this->enumValue($billing->status),
            'period_start' => $billing->period_start?->toDateString(),
            'period_end' => $billing->period_end?->toDateString(),
            'gross_amount' => $this->toDecimal($billing->gross_amount),
            'retention_amount' => $this->toDecimal($billing->retention_amount),
            'other_deductions' => $this->toDecimal($billing->other_deductions_amount),
            'net_amount' => $this->toDecimal($billing->net_amount),
            'paid_amount' => $this->toDecimal($billing->paidAmount()),
            'balance' => $this->toDecimal($billing->balanceAmount()),
            'currency' => $billing->currency,
            'due_date' => $billing->due_date?->toDateString(),
            'collection_status' => $billing->collectionStatus(),
            'project_id' => $billing->project_id,
            'items_count' => $billing->items_count ?? $billing->items()->count(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function estimateItems(Estimate $estimate): array
    {
        return $estimate->items->map(fn ($item) => [
            'description' => $item->description,
            'quantity' => (float) $item->quantity,
            'unit_price' => $this->toDecimal($item->unit_price),
            'total' => $this->toDecimal($item->total),
        ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function invoiceItems(Invoice $invoice): array
    {
        return $invoice->items->map(fn ($item) => [
            'description' => $item->description,
            'quantity' => (float) $item->quantity,
            'unit_price' => $this->toDecimal($item->unit_price),
            'total' => $this->toDecimal($item->total),
        ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function purchaseOrderItems(PurchaseOrder $purchaseOrder): array
    {
        return $purchaseOrder->items->map(fn ($item) => [
            'description' => $item->description,
            'quantity' => (float) $item->quantity,
            'unit_price' => $this->toDecimal($item->unit_price),
            'total' => $this->toDecimal($item->total),
        ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function changeOrderItems(ChangeOrder $changeOrder): array
    {
        return $changeOrder->items->map(fn ($item) => [
            'category' => $this->enumValue($item->category),
            'description' => $item->description,
            'amount' => $this->toDecimal($item->amount),
        ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function budgetItems(Budget $budget): array
    {
        return $budget->items->map(fn ($item) => [
            'description' => $item->description,
            'quantity' => (float) $item->quantity,
            'unit' => $item->unit,
            'unit_price' => $this->toDecimal($item->unit_price),
            'total' => $this->toDecimal($item->total),
            'cost_code_id' => $item->cost_code_id,
            'boq_item_id' => $item->boq_item_id,
        ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function progressReportItems(ProgressReport $report): array
    {
        return $report->items->map(fn ($item) => [
            'description' => $item->description,
            'quantity' => (float) $item->quantity,
            'percent_complete' => (float) $item->percent_complete,
            'accomplished_amount' => $this->toDecimal($item->accomplished_amount),
            'boq_item_id' => $item->boq_item_id,
            'cost_code_id' => $item->cost_code_id,
        ])->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function campaignSummary(MarketingCampaign $campaign): array
    {
        return [
            'uuid' => $campaign->uuid,
            'name' => $campaign->name,
            'description' => $campaign->description,
            'is_active' => (bool) $campaign->is_active,
            'steps_count' => $campaign->steps->count(),
            'steps' => $campaign->steps->map(fn ($step) => [
                'uuid' => $step->uuid,
                'subject' => $step->subject,
                'delay_days' => $step->delay_days,
                'sort_order' => $step->sort_order,
            ])->all(),
        ];
    }
}
