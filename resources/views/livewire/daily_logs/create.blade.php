<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Domains\ProjectManagement\Actions\CreateDailyLogAction;
use App\Domains\ProjectManagement\DTOs\DailyLogData;
use App\Domains\ProjectManagement\Models\Project;
use Carbon\CarbonImmutable;

new #[Layout('layouts.app')] class extends Component {
    public function mount(): void
    {
        $this->project_id = session('active_project_id') ? (string) session('active_project_id') : '';
    }
    #[Validate('required|integer|exists:projects,id')]
    public string $project_id = '';

    #[Validate('required|date')]
    public string $log_date = '';

    #[Validate('nullable|string|max:100')]
    public string $weather = '';

    #[Validate('nullable|integer|min:0')]
    public string $crew_count = '0';

    #[Validate('nullable|numeric|min:0')]
    public string $labor_hours = '0';

    #[Validate('nullable|string')]
    public string $equipment_usage = '';

    #[Validate('required|string')]
    public string $work_performed = '';

    #[Validate('nullable|string')]
    public string $issues = '';

    #[Validate('nullable|string')]
    public string $safety_incidents = '';

    public function with(): array
    {
        return [
            'projects' => Project::where('company_id', auth()->user()->company_id)->latest()->get(),
        ];
    }

    public function save(CreateDailyLogAction $action): void
    {
        $this->validate();

        $action->execute(new DailyLogData(
            projectId: (int) $this->project_id,
            logDate: CarbonImmutable::parse($this->log_date),
            workPerformed: $this->work_performed,
            weather: $this->weather ?: null,
            crewCount: (int) $this->crew_count,
            laborHours: (float) $this->labor_hours,
            equipmentUsage: $this->equipment_usage ?: null,
            issues: $this->issues ?: null,
            safetyIncidents: $this->safety_incidents ?: null,
        ), auth()->user()->company_id, auth()->user()->id);

        session()->flash('status', 'Daily log created.');

        $this->redirect(route('daily_logs.index'), navigate: true);
    }
}; ?>

<div>
    <x-page-header title="Add Daily Log" description="Record a field report for your crew." />

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
                    <x-input-label for="log_date" value="Date" />
                    <div class="mt-2">
                        <x-text-input type="date" wire:model="log_date" id="log_date" />
                    </div>
                    <x-input-error :messages="$errors->get('log_date')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="weather" value="Weather" />
                    <div class="mt-2">
                        <x-text-input type="text" wire:model="weather" id="weather" />
                    </div>
                    <x-input-error :messages="$errors->get('weather')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="crew_count" value="Crew Count" />
                    <div class="mt-2">
                        <x-text-input type="number" wire:model="crew_count" id="crew_count" min="0" />
                    </div>
                    <x-input-error :messages="$errors->get('crew_count')" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="labor_hours" value="Labor Hours" />
                    <div class="mt-2">
                        <x-text-input type="number" step="0.01" wire:model="labor_hours" id="labor_hours" min="0" />
                    </div>
                    <x-input-error :messages="$errors->get('labor_hours')" />
                </div>
                <div class="col-span-full">
                    <x-input-label for="work_performed" value="Work Performed" />
                    <div class="mt-2">
                        <x-textarea id="work_performed" wire:model="work_performed" rows="3"></x-textarea>
                    </div>
                    <x-input-error :messages="$errors->get('work_performed')" />
                </div>
                <div class="col-span-full">
                    <x-input-label for="equipment_usage" value="Equipment Usage" />
                    <div class="mt-2">
                        <x-textarea id="equipment_usage" wire:model="equipment_usage" rows="2"></x-textarea>
                    </div>
                    <x-input-error :messages="$errors->get('equipment_usage')" />
                </div>
                <div class="col-span-full">
                    <x-input-label for="issues" value="Issues" />
                    <div class="mt-2">
                        <x-textarea id="issues" wire:model="issues" rows="2"></x-textarea>
                    </div>
                    <x-input-error :messages="$errors->get('issues')" />
                </div>
                <div class="col-span-full">
                    <x-input-label for="safety_incidents" value="Safety Incidents" />
                    <div class="mt-2">
                        <x-textarea id="safety_incidents" wire:model="safety_incidents" rows="2"></x-textarea>
                    </div>
                    <x-input-error :messages="$errors->get('safety_incidents')" />
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">
            <a href="{{ route('daily_logs.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>
</div>
