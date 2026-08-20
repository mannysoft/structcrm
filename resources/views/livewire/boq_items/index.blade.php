<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Domains\Finance\Actions\DeleteBoqItemAction;
use App\Domains\Finance\Models\BoqItem;

new #[Layout('layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'boqItems' => BoqItem::with(['project', 'costCode'])->latest()->get(),
        ];
    }

    public function delete(DeleteBoqItemAction $action, string $uuid): void
    {
        $boqItem = BoqItem::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($boqItem, 404);

        $this->authorize('delete', $boqItem);

        $action->execute($boqItem);

        session()->flash('status', 'BOQ item deleted.');
    }
}; ?>

<div>
    <x-page-header title="BOQ" description="Bill of quantities line items for your projects.">
        <x-slot name="actions">
            <x-button :href="route('boq_items.import')" variant="secondary">Import BOQ</x-button>
            <x-button :href="route('boq_items.create')">Add item</x-button>
        </x-slot>
    </x-page-header>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Code</x-table.th>
                                <x-table.th>Description</x-table.th>
                                <x-table.th>Project</x-table.th>
                                <x-table.th>Cost Code</x-table.th>
                                <x-table.th>Qty</x-table.th>
                                <x-table.th>Unit</x-table.th>
                                <x-table.th>Unit Price</x-table.th>
                                <x-table.th>Total</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($boqItems as $boqItem)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $boqItem->item_code ?? '—' }}</x-table.td>
                                    <x-table.td>{{ $boqItem->description }}</x-table.td>
                                    <x-table.td>{{ $boqItem->project?->title ?? 'N/A' }}</x-table.td>
                                    <x-table.td>{{ $boqItem->costCode?->name ?? '—' }}</x-table.td>
                                    <x-table.td>{{ $boqItem->quantity }}</x-table.td>
                                    <x-table.td>{{ $boqItem->unit }}</x-table.td>
                                    <x-table.td>{{ number_format($boqItem->unitPrice->toDecimal(), 2) }} {{ $boqItem->currency }}</x-table.td>
                                    <x-table.td>{{ number_format($boqItem->totalAmount->toDecimal(), 2) }} {{ $boqItem->currency }}</x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <a href="{{ route('boq_items.edit', $boqItem) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                                        <x-confirm-delete :uuid="$boqItem->uuid" class="ml-4">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9">
                                        <x-empty-state title="No BOQ items found." description="Add an item or import an Excel BOQ.">
                                            <x-slot name="action">
                                                <x-button :href="route('boq_items.create')">Add item</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>
</div>
