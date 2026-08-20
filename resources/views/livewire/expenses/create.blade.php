<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Common\ValueObjects\Money;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\CreateExpenseAction;
use App\Domains\Sales\DTOs\ExpenseData;
use App\Domains\Sales\Enums\ExpenseCategory;
use Carbon\CarbonImmutable;

new #[Layout('layouts.app')] class extends Component {
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

    public function with(): array
    {
        return [
            'projects' => Project::where('company_id', auth()->user()->company_id)->latest()->get(),
        ];
    }

    public function save(CreateExpenseAction $action): void
    {
        $this->validate();

        $action->execute(new ExpenseData(
            projectId: (int) $this->project_id,
            category: ExpenseCategory::from($this->category),
            amount: Money::fromDecimal((float) $this->amount),
            date: CarbonImmutable::parse($this->date),
            notes: $this->notes ?: null,
        ), auth()->user()->company_id);

        session()->flash('status', 'Expense successfully created.');

        $this->redirect(route('expenses.index'), navigate: true);
    }
}; ?>

<div>
    <x-page-header title="Add Expense" description="Record an operational cost." />

    <form wire:submit="save" class="mt-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                <div class="sm:col-span-3">
                    <x-input-label for="project_id" value="Project" />
                    <div class="mt-2">
                        <x-select id="project_id" wire:model="project_id">
                            <option value="">Select a project</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->title }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('project_id')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="category" value="Category" />
                    <div class="mt-2">
                        <x-select id="category" wire:model="category">
                            <option value="labor">Labor</option>
                            <option value="fuel">Fuel</option>
                            <option value="materials">Materials</option>
                            <option value="equipment">Equipment</option>
                            <option value="subcontract">Subcontract</option>
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('category')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="amount" value="Amount ($)" />
                    <div class="mt-2">
                        <x-text-input type="number" step="0.01" wire:model="amount" id="amount" />
                    </div>
                    <x-input-error :messages="$errors->get('amount')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="date" value="Date" />
                    <div class="mt-2">
                        <x-text-input type="date" wire:model="date" id="date" />
                    </div>
                    <x-input-error :messages="$errors->get('date')" />
                </div>
                <div class="col-span-full">
                    <x-input-label for="notes" value="Notes" />
                    <div class="mt-2">
                        <x-textarea id="notes" wire:model="notes" rows="3"></x-textarea>
                    </div>
                    <x-input-error :messages="$errors->get('notes')" />
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">
            <a href="{{ route('expenses.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>
</div>
