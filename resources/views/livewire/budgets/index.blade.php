<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Domains\Finance\Actions\DeleteBudgetAction;
use App\Domains\Finance\Models\Budget;

new #[Layout('layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'budgets' => Budget::with(['project', 'items'])->latest()->get(),
        ];
    }

    public function delete(DeleteBudgetAction $action, string $uuid): void
    {
        $budget = Budget::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($budget, 404);

        $this->authorize('delete', $budget);

        $action->execute($budget);

        session()->flash('status', 'Budget deleted.');
    }
}; ?>

<div>
    <x-page-header title="Budgets" description="Project budgets and their revisions.">
        <x-slot name="actions">
            <x-button :href="route('budgets.create')">Add budget</x-button>
        </x-slot>
    </x-page-header>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Project</x-table.th>
                                <x-table.th>Type</x-table.th>
                                <x-table.th>Version</x-table.th>
                                <x-table.th>Status</x-table.th>
                                <x-table.th>Total</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($budgets as $budget)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">
                                        {{ $budget->project?->title ?? 'N/A' }}
                                        @if ($budget->is_current)
                                            <x-badge tone="green" class="ml-2">Current</x-badge>
                                        @endif
                                    </x-table.td>
                                    <x-table.td>{{ ucfirst($budget->type->value) }}</x-table.td>
                                    <x-table.td>v{{ $budget->version }}</x-table.td>
                                    <x-table.td>{{ ucfirst($budget->status->value) }}</x-table.td>
                                    <x-table.td>{{ number_format($budget->totalAmount() / 100, 2) }} {{ $budget->currency }}</x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <a href="{{ route('budgets.edit', $budget) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                                        <x-confirm-delete :uuid="$budget->uuid" class="ml-4">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <x-empty-state title="No budgets found." description="Add a budget to get started.">
                                            <x-slot name="action">
                                                <x-button :href="route('budgets.create')">Add budget</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>
</div>
