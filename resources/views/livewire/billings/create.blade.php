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
    <form wire:submit="save" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-3xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                <div class="sm:col-span-3">
                    <label for="project_id" class="block text-sm font-medium leading-6 text-gray-900">Project</label>
                    <div class="mt-2">
                        <select id="project_id" wire:model="project_id" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="">Select a project</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('project_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="progress_report_id" class="block text-sm font-medium leading-6 text-gray-900">Progress Report (optional)</label>
                    <div class="mt-2">
                        <select id="progress_report_id" wire:model="progress_report_id" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="">No report</option>
                            @foreach ($progressReports as $report)
                                <option value="{{ $report->id }}">{{ $report->report_number }} — {{ $report->report_date?->format('M j, Y') }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('progress_report_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="period_start" class="block text-sm font-medium leading-6 text-gray-900">Period Start</label>
                    <div class="mt-2">
                        <input type="date" wire:model="period_start" id="period_start" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('period_start') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="period_end" class="block text-sm font-medium leading-6 text-gray-900">Period End</label>
                    <div class="mt-2">
                        <input type="date" wire:model="period_end" id="period_end" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('period_end') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="status" class="block text-sm font-medium leading-6 text-gray-900">Status</label>
                    <div class="mt-2">
                        <select id="status" wire:model="status" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="draft">Draft</option>
                            <option value="submitted">Submitted</option>
                            <option value="under_review">Under Review</option>
                            <option value="approved">Approved</option>
                            <option value="certified">Certified</option>
                            <option value="due">Due</option>
                            <option value="partially_paid">Partially Paid</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                    @error('status') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="retention_rate" class="block text-sm font-medium leading-6 text-gray-900">Retention Rate (%)</label>
                    <div class="mt-2">
                        <input type="number" step="0.01" min="0" max="100" wire:model="retention_rate" id="retention_rate" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('retention_rate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="other_deductions" class="block text-sm font-medium leading-6 text-gray-900">Other Deductions</label>
                    <div class="mt-2">
                        <input type="number" step="0.01" min="0" wire:model="other_deductions" id="other_deductions" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('other_deductions') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="due_date" class="block text-sm font-medium leading-6 text-gray-900">Due Date</label>
                    <div class="mt-2">
                        <input type="date" wire:model="due_date" id="due_date" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('due_date') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-full">
                    <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Notes</label>
                    <div class="mt-2">
                        <textarea id="notes" wire:model="notes" rows="2" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                    </div>
                    @error('notes') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-full" x-data="{ items: @entangle('items') }">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium leading-6 text-gray-900">Billing Items</h3>
                        <button type="button" @click="items.push({ description: '', quantity: 1, unit_price: '0.00' })" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">Add item</button>
                    </div>
                    <div class="mt-4 space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="grid grid-cols-12 gap-3">
                                <div class="col-span-6">
                                    <input type="text" x-model="item.description" placeholder="Description" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                </div>
                                <div class="col-span-2">
                                    <input type="number" step="0.01" x-model="item.quantity" min="0" placeholder="Qty" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                </div>
                                <div class="col-span-3">
                                    <input type="number" step="0.01" x-model="item.unit_price" placeholder="Unit price" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                </div>
                                <div class="col-span-1 flex items-center">
                                    <button type="button" @click="items.splice(index, 1)" class="text-sm text-red-600 hover:text-red-900">Remove</button>
                                </div>
                            </div>
                        </template>
                    </div>
                    @error('items') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    @error('items.*.description') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end px-4 py-4 border-t border-gray-900/10 gap-x-6 sm:px-8 bg-gray-50 rounded-b-xl">
            <a href="{{ route('billings.index') }}" wire:navigate class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
            <button type="submit" class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
        </div>
    </form>
</div>
