<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\UpdateChangeOrderAction;
use App\Domains\Sales\DTOs\ChangeOrderData;
use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Models\ChangeOrder;

new #[Layout('layouts.app')] class extends Component {
    public string $uuid = '';

    #[Validate('required|integer|exists:projects,id')]
    public string $project_id = '';

    #[Validate('required|string')]
    public string $description = '';

    #[Validate('required|numeric')]
    public string $amount = '';

    #[Validate('required|string')]
    public string $status = 'pending';

    public function mount(ChangeOrder $changeOrder): void
    {
        $this->uuid = $changeOrder->uuid;
        $this->project_id = (string) $changeOrder->project_id;
        $this->description = $changeOrder->description;
        $this->amount = (string) ($changeOrder->amount / 100);
        $this->status = $changeOrder->status->value;
    }

    public function with(): array
    {
        return [
            'projects' => Project::where('company_id', auth()->user()->company_id)->latest()->get(),
        ];
    }

    public function save(UpdateChangeOrderAction $action): void
    {
        $this->validate();

        $changeOrder = ChangeOrder::where('uuid', $this->uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($changeOrder, 404);

        $action->execute($changeOrder, new ChangeOrderData(
            projectId: (int) $this->project_id,
            description: $this->description,
            amountInCents: (int) round((float) $this->amount * 100),
            status: ChangeOrderStatus::from($this->status),
        ));

        session()->flash('status', 'Change order updated.');

        $this->redirect(route('change_orders.index'), navigate: true);
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
                    <label for="status" class="block text-sm font-medium leading-6 text-gray-900">Status</label>
                    <div class="mt-2">
                        <select id="status" wire:model="status" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    @error('status') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="sm:col-span-3">
                    <label for="amount" class="block text-sm font-medium leading-6 text-gray-900">Amount ($)</label>
                    <div class="mt-2">
                        <input type="number" step="0.01" wire:model="amount" id="amount" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('amount') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-full">
                    <label for="description" class="block text-sm font-medium leading-6 text-gray-900">Description</label>
                    <div class="mt-2">
                        <textarea id="description" wire:model="description" rows="4" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                    </div>
                    @error('description') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end px-4 py-4 border-t border-gray-900/10 gap-x-6 sm:px-8 bg-gray-50 rounded-b-xl">
            <a href="{{ route('change_orders.index') }}" wire:navigate class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
            <button type="submit" class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
        </div>
    </form>
</div>
