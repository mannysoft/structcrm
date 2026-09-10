<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\Sales\Actions\DeleteChangeOrderAction;
use App\Domains\Sales\Models\ChangeOrder;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'changeOrders' => ChangeOrder::query()
                ->with('project')
                ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('co_number', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')))
                ->when(session('active_project_id'), fn ($query) => $query->where('project_id', session('active_project_id')))
                ->latest()
                ->paginate(15),
        ];
    }

    public function delete(DeleteChangeOrderAction $action, string $uuid): void
    {
        $changeOrder = ChangeOrder::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($changeOrder, 404);

        $this->authorize('delete', $changeOrder);

        $action->execute($changeOrder);

        session()->flash('status', 'Change order deleted.');
    }
}; ?>

<div>
    <x-page-header title="Change Orders" description="Scope changes and cost adjustments for your projects.">
        <x-slot name="actions">
            <x-button :href="route('change_orders.create')">Add change order</x-button>
        </x-slot>
    </x-page-header>

    <div class="mt-6 flex items-center gap-4">
        <div class="w-full sm:max-w-xs">
            <x-search-input placeholder="Search change orders…" />
        </div>
    </div>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>CO #</x-table.th>
                                <x-table.th>Project</x-table.th>
                                <x-table.th>Description</x-table.th>
                                <x-table.th>Amount</x-table.th>
                                <x-table.th>Status</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($changeOrders as $changeOrder)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $changeOrder->co_number }}</x-table.td>
                                    <x-table.td>{{ $changeOrder->project?->title ?? 'N/A' }}</x-table.td>
                                    <x-table.td>{{ Str::limit($changeOrder->description, 60) }}</x-table.td>
                                    <x-table.td>${{ number_format($changeOrder->amount / 100, 2) }}</x-table.td>
                                    <x-table.td>{{ ucfirst($changeOrder->status->value) }}</x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <a href="{{ route('change_orders.edit', $changeOrder) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                                        <x-confirm-delete :uuid="$changeOrder->uuid" class="ml-4">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <x-empty-state title="No change orders found." description="Add one to get started.">
                                            <x-slot name="action">
                                                <x-button :href="route('change_orders.create')">Add change order</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $changeOrders->links() }}
    </div>
</div>
