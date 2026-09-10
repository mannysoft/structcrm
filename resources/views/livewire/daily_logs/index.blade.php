<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\ProjectManagement\Actions\DeleteDailyLogAction;
use App\Domains\ProjectManagement\Models\DailyLog;

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
            'dailyLogs' => DailyLog::query()
                ->with('project')
                ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('work_performed', 'like', '%'.$this->search.'%')
                    ->orWhere('weather', 'like', '%'.$this->search.'%')
                    ->orWhereHas('project', fn ($q) => $q->where('title', 'like', '%'.$this->search.'%'))))
                ->when(session('active_project_id'), fn ($query) => $query->where('project_id', session('active_project_id')))
                ->latest('log_date')
                ->paginate(15),
        ];
    }

    public function delete(DeleteDailyLogAction $action, string $uuid): void
    {
        $dailyLog = DailyLog::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($dailyLog, 404);

        $this->authorize('delete', $dailyLog);

        $action->execute($dailyLog);

        session()->flash('status', 'Daily log deleted.');
    }
}; ?>

<div>
    <x-page-header title="Daily Logs" description="Field reports submitted by your crew.">
        <x-slot name="actions">
            <x-button :href="route('daily_logs.create')">Add daily log</x-button>
        </x-slot>
    </x-page-header>

    <div class="mt-6 flex items-center gap-4">
        <div class="w-full sm:max-w-xs">
            <x-search-input placeholder="Search daily logs…" />
        </div>
    </div>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Date</x-table.th>
                                <x-table.th>Project</x-table.th>
                                <x-table.th>Weather</x-table.th>
                                <x-table.th>Crew</x-table.th>
                                <x-table.th>Work Performed</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($dailyLogs as $dailyLog)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $dailyLog->log_date?->format('M j, Y') }}</x-table.td>
                                    <x-table.td>{{ $dailyLog->project?->title ?? 'N/A' }}</x-table.td>
                                    <x-table.td>{{ $dailyLog->weather ?? 'N/A' }}</x-table.td>
                                    <x-table.td>{{ $dailyLog->crew_count }}</x-table.td>
                                    <x-table.td>{{ Str::limit($dailyLog->work_performed, 60) }}</x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <a href="{{ route('daily_logs.edit', $dailyLog) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                                        <x-confirm-delete :uuid="$dailyLog->uuid" class="ml-4">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <x-empty-state title="No daily logs found." description="Add one to get started.">
                                            <x-slot name="action">
                                                <x-button :href="route('daily_logs.create')">Add daily log</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $dailyLogs->links() }}
    </div>
</div>
