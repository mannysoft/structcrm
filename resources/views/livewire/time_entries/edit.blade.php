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
    <x-page-header title="Edit Time Entry" description="Update this time entry." />

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
                    <x-input-label for="clock_in_at" value="Clock In" />
                    <div class="mt-2">
                        <x-text-input type="datetime-local" wire:model="clock_in_at" id="clock_in_at" />
                    </div>
                    <x-input-error :messages="$errors->get('clock_in_at')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="clock_out_at" value="Clock Out" />
                    <div class="mt-2">
                        <x-text-input type="datetime-local" wire:model="clock_out_at" id="clock_out_at" />
                    </div>
                    <x-input-error :messages="$errors->get('clock_out_at')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="break_minutes" value="Break (minutes)" />
                    <div class="mt-2">
                        <x-text-input type="number" wire:model="break_minutes" id="break_minutes" min="0" />
                    </div>
                    <x-input-error :messages="$errors->get('break_minutes')" />
                </div>
                <div class="col-span-full">
                    <x-input-label for="notes" value="Notes" />
                    <div class="mt-2">
                        <x-textarea id="notes" wire:model="notes" rows="3"></x-textarea>
                    </div>
                    <x-input-error :messages="$errors->get('notes')" />
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">
            <a href="{{ route('time_entries.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>
</div>
