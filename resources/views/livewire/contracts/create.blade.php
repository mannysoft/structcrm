<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Common\ValueObjects\Money;
use App\Domains\Finance\Actions\CreateContractAction;
use App\Domains\Finance\DTOs\ContractData;
use App\Domains\Finance\Enums\ContractStatus;
use App\Domains\ProjectManagement\Models\Project;
use Carbon\CarbonImmutable;

new #[Layout('layouts.app')] class extends Component {
    #[Validate('required|integer|exists:projects,id')]
    public string $project_id = '';

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|numeric|min:0')]
    public string $contract_value = '';

    #[Validate('required|string')]
    public string $status = 'draft';

    #[Validate('nullable|date')]
    public string $signed_at = '';

    public function with(): array
    {
        $companyId = auth()->user()->company_id;

        return [
            'projects' => Project::where('company_id', $companyId)->latest()->get(),
        ];
    }

    private function companyCurrency(): string
    {
        return auth()->user()->company?->currency?->value ?? 'PHP';
    }

    public function save(CreateContractAction $action): void
    {
        $this->validate();

        $dto = new ContractData(
            projectId: (int) $this->project_id,
            title: $this->title,
            contractValue: Money::fromDecimal((float) $this->contract_value, $this->companyCurrency()),
            status: ContractStatus::from($this->status),
            signedAt: $this->signed_at ? CarbonImmutable::parse($this->signed_at) : null,
        );

        $action->execute($dto, auth()->user()->company_id);

        session()->flash('status', 'Contract created.');

        $this->redirect(route('contracts.index'), navigate: true);
    }
}; ?>

<div>
    <x-page-header title="Add Contract" description="Record a new project contract." />

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
                    <x-input-label for="title" value="Title" />
                    <div class="mt-2">
                        <x-text-input type="text" wire:model="title" id="title" />
                    </div>
                    <x-input-error :messages="$errors->get('title')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="contract_value" value="Contract Value" />
                    <div class="mt-2">
                        <x-text-input type="number" step="0.01" min="0" wire:model="contract_value" id="contract_value" />
                    </div>
                    <x-input-error :messages="$errors->get('contract_value')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="status" value="Status" />
                    <div class="mt-2">
                        <x-select id="status" wire:model="status">
                            <option value="draft">Draft</option>
                            <option value="signed">Signed</option>
                            <option value="completed">Completed</option>
                            <option value="terminated">Terminated</option>
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('status')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="signed_at" value="Signed Date" />
                    <div class="mt-2">
                        <x-text-input type="date" wire:model="signed_at" id="signed_at" />
                    </div>
                    <x-input-error :messages="$errors->get('signed_at')" />
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">
            <a href="{{ route('contracts.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>
</div>
