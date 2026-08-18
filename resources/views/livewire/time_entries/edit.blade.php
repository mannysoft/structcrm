<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Domains\ProjectManagement\Actions\UpdateTimeEntryAction;
use App\Domains\ProjectManagement\DTOs\TimeEntryData;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\TimeEntry;
use Carbon\CarbonImmutable;

new #[Layout('layouts.app')] class extends Component {
    public string $uuid = '';

    #[Validate('required|integer|exists:projects,id')]
    public string $project_id = '';

    #[Validate('required|date')]
    public string $clock_in_at = '';

    #[Validate('nullable|date')]
    public string $clock_out_at = '';

    #[Validate('nullable|integer|min:0')]
    public string $break_minutes = '0';

    #[Validate('nullable|string')]
    public string $notes = '';

    public function mount(TimeEntry $timeEntry): void
    {
        $this->uuid = $timeEntry->uuid;
        $this->project_id = (string) $timeEntry->project_id;
        $this->clock_in_at = $timeEntry->clock_in_at?->format('Y-m-d\TH:i') ?? '';
        $this->clock_out_at = $timeEntry->clock_out_at?->format('Y-m-d\TH:i') ?? '';
        $this->break_minutes = (string) $timeEntry->break_minutes;
        $this->notes = $timeEntry->notes ?? '';
    }

    public function with(): array
    {
        return [
            'projects' => Project::where('company_id', auth()->user()->company_id)->latest()->get(),
        ];
    }

    public function save(UpdateTimeEntryAction $action): void
    {
        $this->validate();

        $entry = TimeEntry::where('uuid', $this->uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($entry, 404);

        $action->execute($entry, new TimeEntryData(
            projectId: (int) $this->project_id,
            clockInAt: CarbonImmutable::parse($this->clock_in_at),
            clockOutAt: $this->clock_out_at !== '' ? CarbonImmutable::parse($this->clock_out_at) : null,
            breakMinutes: (int) $this->break_minutes,
            notes: $this->notes ?: null,
        ));

        session()->flash('status', 'Time entry updated.');

        $this->redirect(route('time_entries.index'), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="save" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-3xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
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
                    <label for="clock_in_at" class="block text-sm font-medium leading-6 text-gray-900">Clock In</label>
                    <div class="mt-2">
                        <input type="datetime-local" wire:model="clock_in_at" id="clock_in_at" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('clock_in_at') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="sm:col-span-3">
                    <label for="clock_out_at" class="block text-sm font-medium leading-6 text-gray-900">Clock Out</label>
                    <div class="mt-2">
                        <input type="datetime-local" wire:model="clock_out_at" id="clock_out_at" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('clock_out_at') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="sm:col-span-3">
                    <label for="break_minutes" class="block text-sm font-medium leading-6 text-gray-900">Break (minutes)</label>
                    <div class="mt-2">
                        <input type="number" wire:model="break_minutes" id="break_minutes" min="0" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('break_minutes') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-full">
                    <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Notes</label>
                    <div class="mt-2">
                        <textarea id="notes" wire:model="notes" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                    </div>
                    @error('notes') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end px-4 py-4 border-t border-gray-900/10 gap-x-6 sm:px-8 bg-gray-50 rounded-b-xl">
            <a href="{{ route('time_entries.index') }}" wire:navigate class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
            <button type="submit" class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
        </div>
    </form>
</div>
