<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Domains\Finance\Actions\CreateBillingAction;
use App\Domains\Finance\DTOs\BillingData;
use App\Domains\Finance\Enums\BillingStatus;
use App\Domains\Finance\Models\ProgressReport;
use App\Domains\ProjectManagement\Models\Project;
use Carbon\CarbonImmutable;

new #[Layout('layouts.app')] class extends Component {
    #[Validate('required|integer|exists:projects,id')]
    public string $project_id = '';

    #[Validate('nullable|integer|exists:progress_reports,id')]
    public string $progress_report_id = '';

    #[Validate('required|date')]
    public string $period_start = '';

    #[Validate('required|date')]
    public string $period_end = '';

    #[Validate('required|string')]
    public string $status = 'draft';

    #[Validate('nullable|numeric|min:0|max:100')]
    public string $retention_rate = '';

    #[Validate('nullable|numeric|min:0')]
    public string $other_deductions = '';

    #[Validate('nullable|date')]
    public string $due_date = '';

    #[Validate('nullable|string')]
    public string $notes = '';

    public array $items = [];

    public function with(): array
    {
        $companyId = auth()->user()->company_id;

        return [
            'projects' => Project::where('company_id', $companyId)->latest()->get(),
            'progressReports' => ProgressReport::where('company_id', $companyId)->latest()->get(),
        ];
    }

    private function companyCurrency(): string
    {
        return auth()->user()->company?->currency?->value ?? 'PHP';
    }

    public function save(CreateBillingAction $action): void
    {
        $this->validate();

        $this->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $items = collect($this->items)->map(fn (array $item) => [
            'description' => $item['description'],
            'quantity' => (float) $item['quantity'],
            'unit_price' => (int) round((float) $item['unit_price'] * 100),
        ])->all();

        $dto = new BillingData(
            projectId: (int) $this->project_id,
            progressReportId: $this->progress_report_id ? (int) $this->progress_report_id : null,
            periodStart: CarbonImmutable::parse($this->period_start),
            periodEnd: CarbonImmutable::parse($this->period_end),
            status: BillingStatus::from($this->status),
            retentionRate: $this->retention_rate !== '' ? (float) $this->retention_rate / 100 : null,
            otherDeductions: (int) round((float) ($this->other_deductions !== '' ? $this->other_deductions : 0) * 100),
            dueDate: $this->due_date ? CarbonImmutable::parse($this->due_date) : null,
            notes: $this->notes ?: null,
            currency: $this->companyCurrency(),
            items: $items,
        );

        $action->execute($dto, auth()->user()->company_id);

        session()->flash('status', 'Billing created.');

        $this->redirect(route('billings.index'), navigate: true);
    }
}; ?>

<div>
    <x-page-header title="Add Billing" description="Create a progress billing." />

    <form wire:submit="save" class="mt-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-3xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                <div class="sm:col-span-3">
                    <x-input-label for="project_id" value="Project" />
                    <div class="mt-2">
                        <x-select id="project_id" wire:model="project_id">
                            <option value="">Select a project</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->title }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('project_id')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="progress_report_id" value="Progress Report (optional)" />
                    <div class="mt-2">
                        <x-select id="progress_report_id" wire:model="progress_report_id">
                            <option value="">No report</option>
                            @foreach ($progressReports as $report)
                                <option value="{{ $report->id }}">{{ $report->report_number }} — {{ $report->report_date?->format('M j, Y') }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('progress_report_id')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="period_start" value="Period Start" />
                    <div class="mt-2">
                        <x-text-input type="date" wire:model="period_start" id="period_start" />
                    </div>
                    <x-input-error :messages="$errors->get('period_start')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="period_end" value="Period End" />
                    <div class="mt-2">
                        <x-text-input type="date" wire:model="period_end" id="period_end" />
                    </div>
                    <x-input-error :messages="$errors->get('period_end')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="status" value="Status" />
                    <div class="mt-2">
                        <x-select id="status" wire:model="status">
                            <option value="draft">Draft</option>
                            <option value="submitted">Submitted</option>
                            <option value="under_review">Under Review</option>
                            <option value="approved">Approved</option>
                            <option value="certified">Certified</option>
                            <option value="due">Due</option>
                            <option value="partially_paid">Partially Paid</option>
                            <option value="paid">Paid</option>
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('status')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="retention_rate" value="Retention Rate (%)" />
                    <div class="mt-2">
                        <x-text-input type="number" step="0.01" min="0" max="100" wire:model="retention_rate" id="retention_rate" />
                    </div>
                    <x-input-error :messages="$errors->get('retention_rate')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="other_deductions" value="Other Deductions" />
                    <div class="mt-2">
                        <x-text-input type="number" step="0.01" min="0" wire:model="other_deductions" id="other_deductions" />
                    </div>
                    <x-input-error :messages="$errors->get('other_deductions')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="due_date" value="Due Date" />
                    <div class="mt-2">
                        <x-text-input type="date" wire:model="due_date" id="due_date" />
                    </div>
                    <x-input-error :messages="$errors->get('due_date')" />
                </div>

                <div class="col-span-full">
                    <x-input-label for="notes" value="Notes" />
                    <div class="mt-2">
                        <x-textarea id="notes" wire:model="notes" rows="2"></x-textarea>
                    </div>
                    <x-input-error :messages="$errors->get('notes')" />
                </div>

                <div class="col-span-full" x-data="{ items: @entangle('items') }">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium leading-6 text-gray-900">Billing Items</h3>
                        <button type="button" @click="items.push({ description: '', quantity: 1, unit_price: '0.00' })" class="text-sm font-semibold text-brand-600 hover:text-brand-500">Add item</button>
                    </div>
                    <div class="mt-4 space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="grid grid-cols-12 gap-3">
                                <div class="col-span-6">
                                    <x-text-input x-model="item.description" placeholder="Description" />
                                </div>
                                <div class="col-span-2">
                                    <x-text-input type="number" step="0.01" x-model="item.quantity" min="0" placeholder="Qty" />
                                </div>
                                <div class="col-span-3">
                                    <x-text-input type="number" step="0.01" x-model="item.unit_price" placeholder="Unit price" />
                                </div>
                                <div class="col-span-1 flex items-center">
                                    <button type="button" @click="items.splice(index, 1)" class="text-sm text-red-600 hover:text-red-900">Remove</button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <x-input-error :messages="$errors->get('items')" />
                    <x-input-error :messages="$errors->get('items.*.description')" />
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">
            <a href="{{ route('billings.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>
</div>
