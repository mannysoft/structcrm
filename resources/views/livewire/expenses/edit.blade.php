<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Common\ValueObjects\Money;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\UpdateExpenseAction;
use App\Domains\Sales\DTOs\ExpenseData;
use App\Domains\Sales\Enums\ExpenseCategory;
use App\Domains\Sales\Models\Expense;
use Carbon\CarbonImmutable;

new #[Layout('layouts.app')] class extends Component {
    public string $uuid = '';

    #[Validate('required|integer|exists:projects,id')]
    public string $project_id = '';

    #[Validate('required|string')]
    public string $category = 'materials';

    #[Validate('required|numeric|min:0')]
    public string $amount = '';

    #[Validate('required|date')]
    public string $date = '';

    #[Validate('nullable|string')]
    public string $notes = '';

    public function mount(Expense $expense): void
    {
        $this->uuid = $expense->uuid;
        $this->project_id = (string) $expense->project_id;
        $this->category = $expense->category->value;
        $this->amount = (string) $expense->expenseAmount->toDecimal();
        $this->date = $expense->date?->format('Y-m-d') ?? '';
        $this->notes = $expense->notes ?? '';
    }

    public function with(): array
    {
        return [
            'projects' => Project::where('company_id', auth()->user()->company_id)->latest()->get(),
        ];
    }

    public function save(UpdateExpenseAction $action): void
    {
        $this->validate();

        $expense = Expense::where('uuid', $this->uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($expense, 404);

        $action->execute($expense, new ExpenseData(
            projectId: (int) $this->project_id,
            category: ExpenseCategory::from($this->category),
            amount: Money::fromDecimal((float) $this->amount),
            date: CarbonImmutable::parse($this->date),
            notes: $this->notes ?: null,
        ));

        session()->flash('status', 'Expense updated.');

        $this->redirect(route('expenses.index'), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="save" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                <div class="sm:col-span-3">
                    <label for="project_id" class="block text-sm font-medium leading-6 text-gray-900">Project</label>
                    <div class="mt-2">
                        <select id="project_id" wire:model="project_id" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="">Select a project</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('project_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="sm:col-span-3">
                    <label for="category" class="block text-sm font-medium leading-6 text-gray-900">Category</label>
                    <div class="mt-2">
                        <select id="category" wire:model="category" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="labor">Labor</option>
                            <option value="fuel">Fuel</option>
                            <option value="materials">Materials</option>
                            <option value="equipment">Equipment</option>
                            <option value="subcontract">Subcontract</option>
                        </select>
                    </div>
                    @error('category') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="sm:col-span-3">
                    <label for="amount" class="block text-sm font-medium leading-6 text-gray-900">Amount ($)</label>
                    <div class="mt-2">
                        <input type="number" step="0.01" wire:model="amount" id="amount" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('amount') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="sm:col-span-3">
                    <label for="date" class="block text-sm font-medium leading-6 text-gray-900">Date</label>
                    <div class="mt-2">
                        <input type="date" wire:model="date" id="date" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('date') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-full">
                    <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Notes</label>
                    <div class="mt-2">
                        <textarea id="notes" wire:model="notes" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                    </div>
                    @error('notes') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end px-4 py-4 border-t border-gray-900/10 gap-x-6 sm:px-8 bg-gray-50 rounded-b-xl">
            <a href="{{ route('expenses.index') }}" wire:navigate class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
            <button type="submit" class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
        </div>
    </form>
</div>
