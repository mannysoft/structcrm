<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Common\ValueObjects\Money;
use App\Domains\ProjectManagement\Actions\UpdateProjectAction;
use App\Domains\ProjectManagement\DTOs\ProjectData;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Carbon\CarbonImmutable;

new #[Layout('layouts.app')] class extends Component {
    public string $uuid = '';

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

    public function mount(Project $project): void
    {
        $this->uuid = $project->uuid;
        $this->title = $project->title;
        $this->client_id = (string) $project->client_id;
        $this->description = $project->description ?? '';
        $this->status = $project->status->value;
        $this->budget = (string) $project->budget->toDecimal();
        $this->start_date = $project->start_date?->format('Y-m-d') ?? '';
        $this->end_date = $project->end_date?->format('Y-m-d') ?? '';
        $this->manager_id = $project->manager_id ? (string) $project->manager_id : '';
    }

    public function with(): array
    {
        $companyId = auth()->user()->company_id;

        return [
            'clients' => Client::where('company_id', $companyId)->latest()->get(),
            'managers' => User::where('company_id', $companyId)->get(),
        ];
    }

    public function save(UpdateProjectAction $action): void
    {
        $this->validate();

        $project = Project::where('uuid', $this->uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($project, 404);

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

        $action->execute($project, $dto);

        session()->flash('status', 'Project updated.');

        $this->redirect(route('projects.index'), navigate: true);
    }
}; ?>

<div>
    <x-page-header title="Edit Project" description="Update this project." />

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
