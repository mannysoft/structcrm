<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Domains\Sales\Actions\DeleteInvoiceAction;
use App\Domains\Sales\Models\Invoice;

new #[Layout('layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'invoices' => Invoice::with('project')->latest()->get(),
        ];
    }

    public function delete(DeleteInvoiceAction $action, string $uuid): void
    {
        $invoice = Invoice::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($invoice, 404);

        $this->authorize('delete', $invoice);

        $action->execute($invoice);

        session()->flash('status', 'Invoice deleted.');
    }
}; ?>

<div>
    <x-page-header title="Invoices" description="Bill your projects and track payments.">
        <x-slot name="actions">
            <x-button :href="route('invoices.create')">Add invoice</x-button>
        </x-slot>
    </x-page-header>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Invoice #</x-table.th>
                                <x-table.th>Project</x-table.th>
                                <x-table.th>Status</x-table.th>
                                <x-table.th>Total</x-table.th>
                                <x-table.th>Due</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($invoices as $invoice)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $invoice->invoice_number }}</x-table.td>
                                    <x-table.td>{{ $invoice->project?->title ?? 'N/A' }}</x-table.td>
                                    <x-table.td>{{ ucfirst($invoice->status->value) }}</x-table.td>
                                    <x-table.td>${{ number_format($invoice->total / 100, 2) }}</x-table.td>
                                    <x-table.td>{{ $invoice->due_date?->format('M j, Y') ?? 'N/A' }}</x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <a href="{{ route('invoices.edit', $invoice) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                                        <x-confirm-delete :uuid="$invoice->uuid" class="ml-4">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <x-empty-state title="No invoices found." description="Add one to get started.">
                                            <x-slot name="action">
                                                <x-button :href="route('invoices.create')">Add invoice</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>
</div>
