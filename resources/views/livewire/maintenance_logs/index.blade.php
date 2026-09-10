<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\ProjectManagement\Actions\DeleteMaintenanceLogAction;
use App\Domains\ProjectManagement\Models\MaintenanceLog;

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
            'logs' => MaintenanceLog::query()
                ->with('equipment')
                ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('description', 'like', '%'.$this->search.'%')
                    ->orWhereHas('equipment', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))))
                ->latest('service_date')
                ->paginate(15),
        ];
    }

    public function delete(DeleteMaintenanceLogAction $action, string $uuid): void
    {
        $log = MaintenanceLog::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($log, 404);

        $this->authorize('delete', $log);

        $action->execute($log);

        session()->flash('status', 'Maintenance log deleted.');
    }
}; ?>

<div>
    <x-page-header title="Maintenance Logs" description="Service history for your fleet and equipment.">
        <x-slot name="actions">
            <x-button :href="route('maintenance_logs.create')">Log service</x-button>
        </x-slot>
    </x-page-header>

    <div class="mt-6 flex items-center gap-4">
        <div class="w-full sm:max-w-xs">
            <x-search-input placeholder="Search maintenance logs…" />
        </div>
    </div>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Equipment</x-table.th>
                                <x-table.th>Service Date</x-table.th>
                                <x-table.th>Description</x-table.th>
                                <x-table.th>Cost</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($logs as $log)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $log->equipment?->name ?? 'N/A' }}</x-table.td>
                                    <x-table.td>{{ $log->service_date?->format('M j, Y') }}</x-table.td>
                                    <x-table.td>{{ $log->description }}</x-table.td>
                                    <x-table.td>{{ $log->cost->currency }} {{ number_format($log->cost->toDecimal(), 2) }}</x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <x-confirm-delete :uuid="$log->uuid">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-empty-state title="No maintenance logs yet." description="Log your first service.">
                                            <x-slot name="action">
                                                <x-button :href="route('maintenance_logs.create')">Log service</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $logs->links() }}
    </div>
</div>
