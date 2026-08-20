<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Domains\Sales\Actions\DeletePurchaseOrderAction;
use App\Domains\Sales\Models\PurchaseOrder;

new #[Layout('layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'purchaseOrders' => PurchaseOrder::with(['project', 'supplier'])->latest()->get(),
        ];
    }

    public function delete(DeletePurchaseOrderAction $action, string $uuid): void
    {
        $purchaseOrder = PurchaseOrder::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($purchaseOrder, 404);

        $this->authorize('delete', $purchaseOrder);

        $action->execute($purchaseOrder);

        session()->flash('status', 'Purchase order deleted.');
    }
}; ?>

<div>
    <x-page-header title="Purchase Orders" description="Material and supply orders sent to suppliers.">
        <x-slot name="actions">
            <x-button :href="route('purchase_orders.create')">Add purchase order</x-button>
        </x-slot>
    </x-page-header>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>PO #</x-table.th>
                                <x-table.th>Project</x-table.th>
                                <x-table.th>Supplier</x-table.th>
                                <x-table.th>Status</x-table.th>
                                <x-table.th>Total</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($purchaseOrders as $purchaseOrder)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $purchaseOrder->po_number }}</x-table.td>
                                    <x-table.td>{{ $purchaseOrder->project?->title ?? 'N/A' }}</x-table.td>
                                    <x-table.td>{{ $purchaseOrder->supplier?->name ?? 'N/A' }}</x-table.td>
                                    <x-table.td>{{ ucfirst($purchaseOrder->status->value) }}</x-table.td>
                                    <x-table.td>${{ number_format($purchaseOrder->total / 100, 2) }}</x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <a href="{{ route('purchase_orders.edit', $purchaseOrder) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                                        <x-confirm-delete :uuid="$purchaseOrder->uuid" class="ml-4">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <x-empty-state title="No purchase orders found." description="Add one to get started.">
                                            <x-slot name="action">
                                                <x-button :href="route('purchase_orders.create')">Add purchase order</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>
</div>
