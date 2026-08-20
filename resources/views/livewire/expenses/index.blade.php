<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Domains\Sales\Actions\DeleteExpenseAction;
use App\Domains\Sales\Models\Expense;

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
            'expenses' => Expense::query()
                ->with('project')
                ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('notes', 'like', '%'.$this->search.'%')
                    ->orWhereHas('project', fn ($q) => $q->where('title', 'like', '%'.$this->search.'%'))))
                ->latest()
                ->paginate(15),
        ];
    }

    public function delete(DeleteExpenseAction $action, string $uuid): void
    {
        $expense = Expense::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($expense, 404);

        $this->authorize('delete', $expense);

        $action->execute($expense);

        session()->flash('status', 'Expense deleted.');
    }
}; ?>

<div>
    <x-page-header title="Expenses" description="Track operational costs across your projects.">
        <x-slot name="actions">
            <x-button :href="route('expenses.create')">Add expense</x-button>
        </x-slot>
    </x-page-header>

    <div class="mt-6 flex items-center gap-4">
        <div class="w-full sm:max-w-xs">
            <x-search-input placeholder="Search expenses…" />
        </div>
    </div>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Project</x-table.th>
                                <x-table.th>Category</x-table.th>
                                <x-table.th>Amount</x-table.th>
                                <x-table.th>Date</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($expenses as $expense)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $expense->project?->title ?? 'N/A' }}</x-table.td>
                                    <x-table.td>{{ ucfirst($expense->category->value) }}</x-table.td>
                                    <x-table.td>${{ number_format($expense->expenseAmount->toDecimal(), 2) }}</x-table.td>
                                    <x-table.td>{{ $expense->date?->format('M j, Y') }}</x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <a href="{{ route('expenses.edit', $expense) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                                        <x-confirm-delete :uuid="$expense->uuid" class="ml-4">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-empty-state title="No expenses found." description="Add an expense to get started.">
                                            <x-slot name="action">
                                                <x-button :href="route('expenses.create')">Add expense</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $expenses->links() }}
    </div>
</div>
