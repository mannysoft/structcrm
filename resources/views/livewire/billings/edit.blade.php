<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Domains\Finance\Actions\RecordBillingPaymentAction;
use App\Domains\Finance\Actions\UpdateBillingAction;
use App\Domains\Finance\DTOs\BillingData;
use App\Domains\Finance\Enums\BillingStatus;
use App\Domains\Finance\Models\Billing;
use App\Domains\Finance\Models\ProgressReport;
use App\Domains\ProjectManagement\Models\Project;
use Carbon\CarbonImmutable;

new #[Layout('layouts.app')] class extends Component {
    public string $uuid = '';

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

    public array $payments = [];

    public string $payment_date = '';

    #[Validate('required|numeric|min:0')]
    public string $payment_amount = '';

    #[Validate('nullable|string|max:50')]
    public string $payment_method = '';

    #[Validate('nullable|string|max:100')]
    public string $payment_reference = '';

    public function mount(Billing $billing): void
    {
        $billing->load(['items', 'payments']);

        $this->uuid = $billing->uuid;
        $this->project_id = (string) $billing->project_id;
        $this->progress_report_id = $billing->progress_report_id ? (string) $billing->progress_report_id : '';
        $this->period_start = $billing->period_start?->format('Y-m-d') ?? '';
        $this->period_end = $billing->period_end?->format('Y-m-d') ?? '';
        $this->status = $billing->status->value;
        $this->retention_rate = $billing->retention_rate !== null ? (string) ($billing->retention_rate * 100) : '';
        $this->other_deductions = (string) ($billing->other_deductions_amount / 100);
        $this->due_date = $billing->due_date?->format('Y-m-d') ?? '';
        $this->notes = $billing->notes ?? '';
        $this->items = $billing->items->map(fn ($item) => [
            'description' => $item->description,
            'quantity' => (string) $item->quantity,
            'unit_price' => (string) ($item->unit_price / 100),
        ])->all();
        $this->payments = $billing->payments->map(fn ($payment) => [
            'payment_date' => $payment->payment_date?->format('Y-m-d'),
            'amount' => $payment->amount / 100,
            'method' => $payment->method,
            'reference' => $payment->reference,
        ])->all();
        $this->payment_date = now()->format('Y-m-d');
    }

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

    public function save(UpdateBillingAction $action): void
    {
        $this->validate();

        $this->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $billing = Billing::where('uuid', $this->uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($billing, 404);

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

        $action->execute($billing, $dto);

        session()->flash('status', 'Billing updated.');

        $this->redirect(route('billings.index'), navigate: true);
    }

    public function addPayment(RecordBillingPaymentAction $action): void
    {
        $this->validateOnly('payment_amount');
        $this->validateOnly('payment_method');
        $this->validateOnly('payment_reference');

        $billing = Billing::where('uuid', $this->uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($billing, 404);

        $billing = $action->execute(
            $billing,
            CarbonImmutable::parse($this->payment_date ?: now()->format('Y-m-d')),
            (int) round((float) $this->payment_amount * 100),
            $this->payment_method ?: null,
            $this->payment_reference ?: null,
        );

        $this->payments = $billing->payments->map(fn ($payment) => [
            'payment_date' => $payment->payment_date?->format('Y-m-d'),
            'amount' => $payment->amount / 100,
            'method' => $payment->method,
            'reference' => $payment->reference,
        ])->all();

        $this->status = $billing->status->value;
        $this->payment_amount = '';
        $this->payment_method = '';
        $this->payment_reference = '';

        session()->flash('status', 'Payment recorded.');
    }
}; ?>

<div>
    <x-page-header title="Edit Billing" description="Update this progress billing." />

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

    <div class="mt-8 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <h3 class="text-base font-semibold leading-7 text-gray-900">Payments</h3>
            <div class="mt-4 flow-root">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Date</th>
                            <th scope="col" class="px-3 py-3 text-left text-sm font-semibold text-gray-900">Amount</th>
                            <th scope="col" class="px-3 py-3 text-left text-sm font-semibold text-gray-900">Method</th>
                            <th scope="col" class="px-3 py-3 text-left text-sm font-semibold text-gray-900">Reference</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($payments as $payment)
                            <tr>
                                <td class="py-3 pl-4 pr-3 text-sm text-gray-500 sm:pl-0">{{ $payment['payment_date'] }}</td>
                                <td class="px-3 py-3 text-sm text-gray-500">{{ number_format($payment['amount'], 2) }}</td>
                                <td class="px-3 py-3 text-sm text-gray-500">{{ $payment['method'] ?? '—' }}</td>
                                <td class="px-3 py-3 text-sm text-gray-500">{{ $payment['reference'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-sm text-center text-gray-500">No payments recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <form wire:submit="addPayment" class="mt-6">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6">
                    <div class="sm:col-span-2">
                        <label for="payment_date" class="block text-sm font-medium leading-6 text-gray-900">Date</label>
                        <div class="mt-2">
                            <input type="date" wire:model="payment_date" id="payment_date" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="payment_amount" class="block text-sm font-medium leading-6 text-gray-900">Amount</label>
                        <div class="mt-2">
                            <input type="number" step="0.01" wire:model="payment_amount" id="payment_amount" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                        @error('payment_amount') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="payment_method" class="block text-sm font-medium leading-6 text-gray-900">Method</label>
                        <div class="mt-2">
                            <input type="text" wire:model="payment_method" id="payment_method" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="payment_reference" class="block text-sm font-medium leading-6 text-gray-900">Reference</label>
                        <div class="mt-2">
                            <input type="text" wire:model="payment_reference" id="payment_reference" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>
                    <div class="sm:col-span-3 flex items-end">
                        <button type="submit" class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500">Record payment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
