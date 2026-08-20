<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Domains\Sales\Actions\DeleteSupplierAction;
use App\Domains\Sales\Models\Supplier;

new #[Layout('layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'suppliers' => Supplier::latest()->get(),
        ];
    }

    public function delete(DeleteSupplierAction $action, string $uuid): void
    {
        $supplier = Supplier::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($supplier, 404);

        $this->authorize('delete', $supplier);

        $action->execute($supplier);

        session()->flash('status', 'Supplier deleted.');
    }
}; ?>

<div>
    <x-page-header title="Suppliers" description="A directory of your company's suppliers.">
        <x-slot name="actions">
            <x-button :href="route('suppliers.create')">Add supplier</x-button>
        </x-slot>
    </x-page-header>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Name</x-table.th>
                                <x-table.th>Email</x-table.th>
                                <x-table.th>Phone</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($suppliers as $supplier)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $supplier->name }}</x-table.td>
                                    <x-table.td>{{ $supplier->email ?? 'N/A' }}</x-table.td>
                                    <x-table.td>{{ $supplier->phone ?? 'N/A' }}</x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <a href="{{ route('suppliers.edit', $supplier) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                                        <x-confirm-delete :uuid="$supplier->uuid" class="ml-4">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <x-empty-state title="No suppliers found." description="Add a supplier to get started.">
                                            <x-slot name="action">
                                                <x-button :href="route('suppliers.create')">Add supplier</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>
</div>
