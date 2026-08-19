<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
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
    <form wire:submit="save" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                
                <div class="sm:col-span-4">
                    <label for="title" class="block text-sm font-medium leading-6 text-gray-900">Title</label>
                    <div class="mt-2">
                        <input type="text" wire:model="title" id="title" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('title') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="client_id" class="block text-sm font-medium leading-6 text-gray-900">Client</label>
                    <div class="mt-2">
                        <select id="client_id" wire:model="client_id" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="">Select a client</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('client_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="manager_id" class="block text-sm font-medium leading-6 text-gray-900">Manager</label>
                    <div class="mt-2">
                        <select id="manager_id" wire:model="manager_id" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="">No manager</option>
                            @foreach ($managers as $manager)
                                <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('manager_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-full">
                    <label for="description" class="block text-sm font-medium leading-6 text-gray-900">Description</label>
                    <div class="mt-2">
                        <textarea id="description" wire:model="description" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                    </div>
                    @error('description') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="status" class="block text-sm font-medium leading-6 text-gray-900">Status</label>
                    <div class="mt-2">
                        <select id="status" wire:model="status" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="on_hold">On Hold</option>
                            <option value="completed">Completed</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    @error('status') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="budget" class="block text-sm font-medium leading-6 text-gray-900">Budget ($)</label>
                    <div class="mt-2">
                        <input type="number" step="0.01" wire:model="budget" id="budget" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('budget') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="start_date" class="block text-sm font-medium leading-6 text-gray-900">Start Date</label>
                    <div class="mt-2">
                        <input type="date" wire:model="start_date" id="start_date" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('start_date') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="end_date" class="block text-sm font-medium leading-6 text-gray-900">End Date</label>
                    <div class="mt-2">
                        <input type="date" wire:model="end_date" id="end_date" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('end_date') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end px-4 py-4 border-t border-gray-900/10 gap-x-6 sm:px-8 bg-gray-50 rounded-b-xl">
            <a href="/projects" wire:navigate class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
            <button type="submit" class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
        </div>
    </form>
</div>
