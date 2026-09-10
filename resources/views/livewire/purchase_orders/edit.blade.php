<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\UpdatePurchaseOrderAction;
use App\Domains\Sales\DTOs\PurchaseOrderData;
use App\Domains\Sales\Enums\PurchaseOrderStatus;
use App\Domains\Sales\Models\PurchaseOrder;
use App\Domains\Sales\Models\Supplier;

new #[Layout('layouts.app')] class extends Component {
    public string $uuid = '';

    #[Validate('required|integer|exists:projects,id')]
    public string $project_id = '';

    #[Validate('required|integer|exists:suppliers,id')]
    public string $supplier_id = '';

    #[Validate('required|string')]
    public string $status = 'draft';

    public array $items = [];

    public function mount(PurchaseOrder $purchaseOrder): void
    {
        $this->uuid = $purchaseOrder->uuid;
        $this->project_id = (string) $purchaseOrder->project_id;
        $this->supplier_id = (string) $purchaseOrder->supplier_id;
        $this->status = $purchaseOrder->status->value;
        $this->items = $purchaseOrder->items->map(fn ($item) => [
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit_price' => (string) ($item->unit_price / 100),
        ])->all();
    }

    public function with(): array
    {
        $companyId = auth()->user()->company_id;

        return [
            'projects' => Project::where('company_id', $companyId)->latest()->get(),
            'suppliers' => Supplier::where('company_id', $companyId)->latest()->get(),
        ];
    }

    public function save(UpdatePurchaseOrderAction $action): void
    {
        $this->validate();

        $this->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $purchaseOrder = PurchaseOrder::where('uuid', $this->uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($purchaseOrder, 404);

        $items = collect($this->items)->map(fn (array $item) => [
            'description' => $item['description'],
            'quantity' => (int) $item['quantity'],
            'unit_price' => (int) round((float) $item['unit_price'] * 100),
        ])->all();

        $action->execute($purchaseOrder, new PurchaseOrderData(
            projectId: (int) $this->project_id,
            supplierId: (int) $this->supplier_id,
            status: PurchaseOrderStatus::from($this->status),
            items: $items,
        ));

        session()->flash('status', 'Purchase order updated.');

        $this->redirect(route('purchase_orders.index'), navigate: true);
    }
}; ?>

<div>
    <x-page-header title="Edit Purchase Order" description="Update this purchase order." />

    <form wire:submit="save" class="mt-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-3xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
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
                    <x-input-label for="supplier_id" value="Supplier" />
                    <div class="mt-2">
                        <x-select id="supplier_id" wire:model="supplier_id">
                            <option value="">Select a supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('supplier_id')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="status" value="Status" />
                    <div class="mt-2">
                        <x-select id="status" wire:model="status">
                            <option value="draft">Draft</option>
                            <option value="sent">Sent</option>
                            <option value="approved">Approved</option>
                            <option value="received">Received</option>
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('status')" />
                </div>

                <div class="col-span-full" x-data="{ items: @entangle('items') }">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium leading-6 text-gray-900">Line Items</h3>
                        <button type="button" @click="items.push({ description: '', quantity: 1, unit_price: '0.00' })" class="text-sm font-semibold text-brand-600 hover:text-brand-500">Add item</button>
                    </div>
                    <div class="mt-4 space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="grid grid-cols-12 gap-3">
                                <div class="col-span-6">
                                    <x-text-input x-model="item.description" placeholder="Description" />
                                </div>
                                <div class="col-span-2">
                                    <x-text-input type="number" x-model="item.quantity" min="1" placeholder="Qty" />
                                </div>
                                <div class="col-span-3">
                                    <x-text-input type="number" step="0.01" x-model="item.unit_price" placeholder="Unit price" />
                                </div>
                                <div class="col-span-1 flex items-center">
                                    <button type="button" @click="items.splice(index, 1)" class="text-sm text-red-600 hover:text-red-900">Remove</button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <x-input-error :messages="$errors->get('items')" />
                    <x-input-error :messages="$errors->get('items.*.description')" />
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">
            <a href="{{ route('purchase_orders.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>
</div>
