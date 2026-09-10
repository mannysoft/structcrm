<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\Finance\Actions\DeleteBillingAction;
use App\Domains\Finance\Models\Billing;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'billings' => Billing::query()
                ->with(['project', 'payments'])
                ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('billing_number', 'like', '%'.$this->search.'%')
                    ->orWhere('notes', 'like', '%'.$this->search.'%')
                    ->orWhereHas('project', fn ($q) => $q->where('title', 'like', '%'.$this->search.'%'))))
                ->when(session('active_project_id'), fn ($query) => $query->where('project_id', session('active_project_id')))
                ->latest()
                ->paginate(15),
        ];
    }

    public function delete(DeleteBillingAction $action, string $uuid): void
    {
        $billing = Billing::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($billing, 404);

        $this->authorize('delete', $billing);

        $action->execute($billing);

        session()->flash('status', 'Billing deleted.');
    }
}; ?>

<div>
    <x-page-header title="Progress Billings" description="Progress billings with retention, deductions, and collection status.">
        <x-slot name="actions">
            <x-button :href="route('billings.create')">Add billing</x-button>
        </x-slot>
    </x-page-header>

    <div class="mt-6 flex items-center gap-4">
        <div class="w-full sm:max-w-xs">
            <x-search-input placeholder="Search billings…" />
        </div>
    </div>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Number</x-table.th>
                                <x-table.th>Project</x-table.th>
                                <x-table.th>Period</x-table.th>
                                <x-table.th>Net Billing</x-table.th>
                                <x-table.th>Balance</x-table.th>
                                <x-table.th>Status</x-table.th>
                                <x-table.th>Collection</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($billings as $billing)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $billing->billing_number }}</x-table.td>
                                    <x-table.td>{{ $billing->project?->title ?? 'N/A' }}</x-table.td>
                                    <x-table.td>{{ $billing->period_start?->format('M j') }} – {{ $billing->period_end?->format('M j, Y') }}</x-table.td>
                                    <x-table.td>{{ number_format($billing->netAmount()->toDecimal(), 2) }} {{ $billing->currency }}</x-table.td>
                                    <x-table.td>{{ number_format($billing->balanceAmount() / 100, 2) }} {{ $billing->currency }}</x-table.td>
                                    <x-table.td>
                                        <x-badge tone="sky">{{ ucfirst(str_replace('_', ' ', $billing->status->value)) }}</x-badge>
                                    </x-table.td>
                                    <x-table.td>
                                        @php
                                            $collectionTone = match ($billing->collectionStatus()) {
                                                'paid' => 'green',
                                                'overdue' => 'red',
                                                'partially_paid' => 'amber',
                                                default => 'gray',
                                            };
                                        @endphp
                                        <x-badge :tone="$collectionTone">{{ ucfirst(str_replace('_', ' ', $billing->collectionStatus())) }}</x-badge>
                                    </x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <a href="{{ route('billings.edit', $billing) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                                        <x-confirm-delete :uuid="$billing->uuid" class="ml-4">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <x-empty-state title="No billings found." description="Add a billing to get started.">
                                            <x-slot name="action">
                                                <x-button :href="route('billings.create')">Add billing</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $billings->links() }}
    </div>
</div>
