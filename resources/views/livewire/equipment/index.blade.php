<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\ProjectManagement\Actions\DeleteEquipmentAction;
use App\Domains\ProjectManagement\Models\Equipment;

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
            'equipment' => Equipment::query()
                ->withCount('maintenanceLogs')
                ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('notes', 'like', '%'.$this->search.'%')))
                ->latest()
                ->paginate(15),
        ];
    }

    public function delete(DeleteEquipmentAction $action, string $uuid): void
    {
        $item = Equipment::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($item, 404);

        $this->authorize('delete', $item);

        $action->execute($item);

        session()->flash('status', 'Equipment deleted.');
    }
}; ?>

<div>
    <x-page-header title="Fleet & Equipment" description="Machinery, tools and vehicles with maintenance schedules.">
        <x-slot name="actions">
            <x-button :href="route('equipment.create')">Add equipment</x-button>
        </x-slot>
    </x-page-header>

    <div class="mt-6 flex items-center gap-4">
        <div class="w-full sm:max-w-xs">
            <x-search-input placeholder="Search equipment…" />
        </div>
    </div>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Name</x-table.th>
                                <x-table.th>Type</x-table.th>
                                <x-table.th>Status</x-table.th>
                                <x-table.th>Service Due</x-table.th>
                                <x-table.th>Logs</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($equipment as $item)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $item->name }}</x-table.td>
                                    <x-table.td>{{ $item->type->value }}</x-table.td>
                                    <x-table.td>
                                        <x-badge :tone="$item->status === \App\Domains\ProjectManagement\Enums\EquipmentStatus::Maintenance ? 'amber' : 'green'">{{ $item->status->value }}</x-badge>
                                    </x-table.td>
                                    <x-table.td>{{ $item->service_due_at?->format('M j, Y') ?? '—' }}</x-table.td>
                                    <x-table.td>{{ $item->maintenance_logs_count }}</x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <a href="{{ route('equipment.edit', $item) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                                        <x-confirm-delete :uuid="$item->uuid" class="ml-4">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <x-empty-state title="No equipment yet." description="Add your first fleet item.">
                                            <x-slot name="action">
                                                <x-button :href="route('equipment.create')">Add equipment</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $equipment->links() }}
    </div>
</div>
