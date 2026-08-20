<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Domains\Sales\Actions\DeleteEstimateAction;
use App\Domains\Sales\Models\Estimate;

new #[Layout('layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'estimates' => Estimate::latest()->get(),
        ];
    }

    public function delete(DeleteEstimateAction $action, string $uuid): void
    {
        $estimate = Estimate::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($estimate, 404);

        $this->authorize('delete', $estimate);

        $action->execute($estimate);

        session()->flash('status', 'Estimate deleted.');
    }
}; ?>

<div>
    <x-page-header title="Estimates" description="Build and send quotations to your clients.">
        <x-slot name="actions">
            <x-button :href="route('estimates.create')">Add estimate</x-button>
        </x-slot>
    </x-page-header>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Estimate #</x-table.th>
                                <x-table.th>Title</x-table.th>
                                <x-table.th>Status</x-table.th>
                                <x-table.th>Total</x-table.th>
                                <x-table.th>Valid Until</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($estimates as $estimate)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $estimate->estimate_number }}</x-table.td>
                                    <x-table.td class="font-medium text-gray-900">{{ $estimate->title }}</x-table.td>
                                    <x-table.td>{{ ucfirst($estimate->status->value) }}</x-table.td>
                                    <x-table.td>${{ number_format($estimate->amount / 100, 2) }}</x-table.td>
                                    <x-table.td>{{ $estimate->valid_until?->format('M j, Y') ?? 'N/A' }}</x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <a href="{{ route('estimates.edit', $estimate) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                                        <x-confirm-delete :uuid="$estimate->uuid" class="ml-4">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <x-empty-state title="No estimates found." description="Add one to get started.">
                                            <x-slot name="action">
                                                <x-button :href="route('estimates.create')">Add estimate</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>
</div>
