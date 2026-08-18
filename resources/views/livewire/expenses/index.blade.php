<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Domains\Sales\Actions\DeleteExpenseAction;
use App\Domains\Sales\Models\Expense;

new #[Layout('layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'expenses' => Expense::with('project')->latest()->get(),
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
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">Expenses</h1>
            <p class="mt-2 text-sm text-gray-700">Track operational costs across your projects.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('expenses.create') }}" wire:navigate class="block px-3 py-2 text-sm font-semibold text-center text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Add expense
            </a>
        </div>
    </div>

    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Project</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Category</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Amount</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($expenses as $expense)
                                <tr>
                                    <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-6">{{ $expense->project?->title ?? 'N/A' }}</td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ ucfirst($expense->category->value) }}</td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">${{ number_format($expense->expenseAmount->toDecimal(), 2) }}</td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $expense->date?->format('M j, Y') }}</td>
                                    <td class="relative py-4 pl-3 pr-4 text-sm font-medium text-right whitespace-nowrap sm:pr-6">
                                        <a href="{{ route('expenses.edit', $expense) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        <button wire:click="delete('{{ $expense->uuid }}')" wire:confirm="Are you sure you want to delete this expense?" class="ml-4 text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-8 text-sm text-center text-gray-500">
                                        No expenses found. Add an expense to get started.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
