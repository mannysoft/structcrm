<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Domains\Finance\Actions\DeleteContractAction;
use App\Domains\Finance\Models\Contract;

new #[Layout('layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'contracts' => Contract::with('project.changeOrders')->latest()->get(),
        ];
    }

    public function delete(DeleteContractAction $action, string $uuid): void
    {
        $contract = Contract::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($contract, 404);

        $this->authorize('delete', $contract);

        $action->execute($contract);

        session()->flash('status', 'Contract deleted.');
    }
}; ?>

<div>
    <x-page-header title="Contracts" description="A list of contracts and their current and revised values.">
        <x-slot name="actions">
            <x-button :href="route('contracts.create')">Add contract</x-button>
        </x-slot>
    </x-page-header>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Number</x-table.th>
                                <x-table.th>Title</x-table.th>
                                <x-table.th>Project</x-table.th>
                                <x-table.th>Value</x-table.th>
                                <x-table.th>Revised</x-table.th>
                                <x-table.th>Status</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($contracts as $contract)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $contract->contract_number }}</x-table.td>
                                    <x-table.td>{{ $contract->title }}</x-table.td>
                                    <x-table.td>{{ $contract->project?->title ?? 'N/A' }}</x-table.td>
                                    <x-table.td>{{ number_format($contract->contractValue->toDecimal(), 2) }} {{ $contract->contract_value_currency }}</x-table.td>
                                    <x-table.td>{{ number_format($contract->revisedValue()->toDecimal(), 2) }} {{ $contract->contract_value_currency }}</x-table.td>
                                    <x-table.td>
                                        <x-badge tone="sky">{{ ucfirst($contract->status->value) }}</x-badge>
                                    </x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <a href="{{ route('contracts.edit', $contract) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                                        <x-confirm-delete :uuid="$contract->uuid" class="ml-4">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <x-empty-state title="No contracts found." description="Add a contract to get started.">
                                            <x-slot name="action">
                                                <x-button :href="route('contracts.create')">Add contract</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>
</div>
