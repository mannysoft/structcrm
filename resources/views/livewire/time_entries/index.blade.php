<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Domains\ProjectManagement\Actions\DeleteTimeEntryAction;
use App\Domains\ProjectManagement\Models\TimeEntry;

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
            'entries' => TimeEntry::query()
                ->with('project')
                ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('notes', 'like', '%'.$this->search.'%')
                    ->orWhereHas('project', fn ($q) => $q->where('title', 'like', '%'.$this->search.'%'))))
                ->latest('clock_in_at')
                ->paginate(15),
        ];
    }

    public function delete(DeleteTimeEntryAction $action, string $uuid): void
    {
        $entry = TimeEntry::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($entry, 404);

        $this->authorize('delete', $entry);

        $action->execute($entry);

        session()->flash('status', 'Time entry deleted.');
    }
}; ?>

<div>
    <x-page-header title="Time Tracking" description="Clock-ins and worked hours across your projects.">
        <x-slot name="actions">
            <x-button :href="route('time_entries.create')">Clock in</x-button>
        </x-slot>
    </x-page-header>

    <div class="mt-6 flex items-center gap-4">
        <div class="w-full sm:max-w-xs">
            <x-search-input placeholder="Search time entries…" />
        </div>
    </div>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Project</x-table.th>
                                <x-table.th>Clock In</x-table.th>
                                <x-table.th>Clock Out</x-table.th>
                                <x-table.th>Duration</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($entries as $entry)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $entry->project?->title ?? 'N/A' }}</x-table.td>
                                    <x-table.td>{{ $entry->clock_in_at?->format('M j, Y g:i A') }}</x-table.td>
                                    <x-table.td>
                                        @if ($entry->isRunning())
                                            <x-badge tone="green">Running</x-badge>
                                        @else
                                            <span class="text-gray-500">{{ $entry->clock_out_at?->format('M j, Y g:i A') }}</span>
                                        @endif
                                    </x-table.td>
                                    <x-table.td>
                                        @if ($entry->durationMinutes() !== null)
                                            {{ intdiv($entry->durationMinutes(), 60) }}h {{ $entry->durationMinutes() % 60 }}m
                                        @else
                                            —
                                        @endif
                                    </x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <a href="{{ route('time_entries.edit', $entry) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                                        <x-confirm-delete :uuid="$entry->uuid" class="ml-4">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-empty-state title="No time entries yet." description="Clock in to get started.">
                                            <x-slot name="action">
                                                <x-button :href="route('time_entries.create')">Clock in</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $entries->links() }}
    </div>
</div>
