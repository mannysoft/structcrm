<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\UpdateChangeOrderAction;
use App\Domains\Sales\DTOs\ChangeOrderData;
use App\Domains\Sales\Enums\ChangeOrderItemType;
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

    public array $items = [];

    public function mount(ChangeOrder $changeOrder): void
    {
        $changeOrder->load('items');

        $this->uuid = $changeOrder->uuid;
        $this->project_id = (string) $changeOrder->project_id;
        $this->description = $changeOrder->description;
        $this->amount = (string) ($changeOrder->amount / 100);
        $this->status = $changeOrder->status->value;
        $this->items = $changeOrder->items->map(fn ($item) => [
            'category' => $item->category->value,
            'description' => $item->description,
            'amount' => (string) ($item->amount / 100),
        ])->all();
    }

    public function with(): array
    {
        return [
            'projects' => Project::where('company_id', auth()->user()->company_id)->latest()->get(),
            'itemTypes' => ChangeOrderItemType::cases(),
        ];
    }

    private function companyCurrency(): string
    {
        return auth()->user()->company?->currency?->value ?? 'PHP';
    }

    public function save(UpdateChangeOrderAction $action): void
    {
        $this->validate();

        $this->validate([
            'items' => ['nullable', 'array'],
            'items.*.category' => ['required', 'string'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.amount' => ['required', 'numeric'],
        ]);

        $changeOrder = ChangeOrder::where('uuid', $this->uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($changeOrder, 404);

        $items = collect($this->items)->map(fn (array $item) => [
            'category' => ChangeOrderItemType::from($item['category'])->value,
            'description' => $item['description'],
            'amount' => (int) round((float) $item['amount'] * 100),
        ])->all();

        $action->execute($changeOrder, new ChangeOrderData(
            projectId: (int) $this->project_id,
            description: $this->description,
            amountInCents: (int) round((float) $this->amount * 100),
            status: ChangeOrderStatus::from($this->status),
            amountCurrency: $this->companyCurrency(),
            items: $items,
        ));

        session()->flash('status', 'Change order updated.');

        $this->redirect(route('change_orders.index'), navigate: true);
    }
}; ?>

<div>
    <x-page-header title="Edit Change Order" description="Update this contract variation." />

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
                    <x-input-label for="status" value="Status" />
                    <div class="mt-2">
                        <x-select id="status" wire:model="status">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('status')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="amount" value="Amount" />
                    <div class="mt-2">
                        <x-text-input type="number" step="0.01" wire:model="amount" id="amount" />
                    </div>
                    <x-input-error :messages="$errors->get('amount')" />
                </div>
                <div class="col-span-full">
                    <x-input-label for="description" value="Description" />
                    <div class="mt-2">
                        <x-textarea id="description" wire:model="description" rows="4"></x-textarea>
                    </div>
                    <x-input-error :messages="$errors->get('description')" />
                </div>

                <div class="col-span-full" x-data="{ items: @entangle('items') }">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium leading-6 text-gray-900">Cost Breakdown</h3>
                        <button type="button" @click="items.push({ category: 'material', description: '', amount: '0.00' })" class="text-sm font-semibold text-brand-600 hover:text-brand-500">Add item</button>
                    </div>
                    <div class="mt-4 space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="grid grid-cols-12 gap-3">
                                <div class="col-span-3">
                                    <x-select x-model="item.category">
                                        @foreach ($itemTypes as $type)
                                            <option value="{{ $type->value }}">{{ ucfirst($type->value) }}</option>
                                        @endforeach
                                    </x-select>
                                </div>
                                <div class="col-span-5">
                                    <x-text-input x-model="item.description" placeholder="Description" />
                                </div>
                                <div class="col-span-3">
                                    <x-text-input type="number" step="0.01" x-model="item.amount" placeholder="Amount" />
                                </div>
                                <div class="col-span-1 flex items-center">
                                    <button type="button" @click="items.splice(index, 1)" class="text-sm text-red-600 hover:text-red-900">Remove</button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <x-input-error :messages="$errors->get('items.*.description')" />
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">
            <a href="{{ route('change_orders.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>
</div>
