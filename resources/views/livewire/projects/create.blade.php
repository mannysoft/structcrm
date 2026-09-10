<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Domains\ProjectManagement\Actions\CreateProjectAction;
use App\Domains\ProjectManagement\DTOs\ProjectData;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Common\ValueObjects\Money;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Carbon\CarbonImmutable;

new #[Layout('layouts.app')] class extends Component {
    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|integer|exists:clients,id')]
    public string $client_id = '';

    #[Validate('nullable|string')]
    public string $description = '';
    
    #[Validate('required|string')]
    public string $status = 'draft';
    
    #[Validate('required|numeric|min:0')]
    public string $budget = '';
    
    #[Validate('nullable|date')]
    public string $start_date = '';

    #[Validate('nullable|date|after_or_equal:start_date')]
    public string $end_date = '';

    #[Validate('nullable|integer|exists:users,id')]
    public string $manager_id = '';

    public function with(): array
    {
        $companyId = auth()->user()->company_id;

        return [
            'clients' => Client::where('company_id', $companyId)->latest()->get(),
            'managers' => User::where('company_id', $companyId)->get(),
        ];
    }

    public function save(CreateProjectAction $action): void
    {
        $this->validate();

        $dto = new ProjectData(
            title: $this->title,
            clientId: (int) $this->client_id,
            status: ProjectStatus::from($this->status),
            budget: Money::fromDecimal((float) $this->budget),
            description: $this->description ?: null,
            managerId: $this->manager_id ? (int) $this->manager_id : null,
            startDate: $this->start_date ? CarbonImmutable::parse($this->start_date) : null,
            endDate: $this->end_date ? CarbonImmutable::parse($this->end_date) : null,
        );

        $action->execute($dto, auth()->user()->company_id);

        session()->flash('status', 'Project successfully created.');

        $this->redirect(route('projects.index'), navigate: true);
    }
}; ?>

<div>
    <x-page-header title="Add Project" description="Create a new construction project." />

    <form wire:submit="save" class="mt-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                <div class="sm:col-span-4">
                    <x-input-label for="title" value="Title" />
                    <div class="mt-2">
                        <x-text-input type="text" wire:model="title" id="title" />
                    </div>
                    <x-input-error :messages="$errors->get('title')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="client_id" value="Client" />
                    <div class="mt-2">
                        <x-select id="client_id" wire:model="client_id">
                            <option value="">Select a client</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('client_id')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="manager_id" value="Manager" />
                    <div class="mt-2">
                        <x-select id="manager_id" wire:model="manager_id">
                            <option value="">No manager</option>
                            @foreach ($managers as $manager)
                                <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('manager_id')" />
                </div>

                <div class="col-span-full">
                    <x-input-label for="description" value="Description" />
                    <div class="mt-2">
                        <x-textarea id="description" wire:model="description" rows="3"></x-textarea>
                    </div>
                    <x-input-error :messages="$errors->get('description')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="status" value="Status" />
                    <div class="mt-2">
                        <x-select id="status" wire:model="status">
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="on_hold">On Hold</option>
                            <option value="completed">Completed</option>
                            <option value="archived">Archived</option>
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('status')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="budget" value="Budget ($)" />
                    <div class="mt-2">
                        <x-text-input type="number" step="0.01" wire:model="budget" id="budget" />
                    </div>
                    <x-input-error :messages="$errors->get('budget')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="start_date" value="Start Date" />
                    <div class="mt-2">
                        <x-text-input type="date" wire:model="start_date" id="start_date" />
                    </div>
                    <x-input-error :messages="$errors->get('start_date')" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="end_date" value="End Date" />
                    <div class="mt-2">
                        <x-text-input type="date" wire:model="end_date" id="end_date" />
                    </div>
                    <x-input-error :messages="$errors->get('end_date')" />
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">
            <a href="{{ route('projects.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>
</div>
