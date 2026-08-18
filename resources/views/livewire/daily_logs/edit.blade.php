<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Domains\ProjectManagement\Actions\UpdateDailyLogAction;
use App\Domains\ProjectManagement\DTOs\DailyLogData;
use App\Domains\ProjectManagement\Models\DailyLog;
use App\Domains\ProjectManagement\Models\Project;
use Carbon\CarbonImmutable;

new #[Layout('layouts.app')] class extends Component {
    public string $uuid = '';

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

    public function mount(DailyLog $dailyLog): void
    {
        $this->uuid = $dailyLog->uuid;
        $this->project_id = (string) $dailyLog->project_id;
        $this->log_date = $dailyLog->log_date?->format('Y-m-d') ?? '';
        $this->weather = $dailyLog->weather ?? '';
        $this->crew_count = (string) $dailyLog->crew_count;
        $this->labor_hours = (string) $dailyLog->labor_hours;
        $this->equipment_usage = $dailyLog->equipment_usage ?? '';
        $this->work_performed = $dailyLog->work_performed;
        $this->issues = $dailyLog->issues ?? '';
        $this->safety_incidents = $dailyLog->safety_incidents ?? '';
    }

    public function with(): array
    {
        return [
            'projects' => Project::where('company_id', auth()->user()->company_id)->latest()->get(),
        ];
    }

    public function save(UpdateDailyLogAction $action): void
    {
        $this->validate();

        $dailyLog = DailyLog::where('uuid', $this->uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($dailyLog, 404);

        $action->execute($dailyLog, new DailyLogData(
            projectId: (int) $this->project_id,
            logDate: CarbonImmutable::parse($this->log_date),
            workPerformed: $this->work_performed,
            weather: $this->weather ?: null,
            crewCount: (int) $this->crew_count,
            laborHours: (float) $this->labor_hours,
            equipmentUsage: $this->equipment_usage ?: null,
            issues: $this->issues ?: null,
            safetyIncidents: $this->safety_incidents ?: null,
        ));

        session()->flash('status', 'Daily log updated.');

        $this->redirect(route('daily_logs.index'), navigate: true);
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
                    <label for="log_date" class="block text-sm font-medium leading-6 text-gray-900">Date</label>
                    <div class="mt-2">
                        <input type="date" wire:model="log_date" id="log_date" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('log_date') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="sm:col-span-3">
                    <label for="weather" class="block text-sm font-medium leading-6 text-gray-900">Weather</label>
                    <div class="mt-2">
                        <input type="text" wire:model="weather" id="weather" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('weather') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="sm:col-span-3">
                    <label for="crew_count" class="block text-sm font-medium leading-6 text-gray-900">Crew Count</label>
                    <div class="mt-2">
                        <input type="number" wire:model="crew_count" id="crew_count" min="0" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('crew_count') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="sm:col-span-3">
                    <label for="labor_hours" class="block text-sm font-medium leading-6 text-gray-900">Labor Hours</label>
                    <div class="mt-2">
                        <input type="number" step="0.01" wire:model="labor_hours" id="labor_hours" min="0" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('labor_hours') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-full">
                    <label for="work_performed" class="block text-sm font-medium leading-6 text-gray-900">Work Performed</label>
                    <div class="mt-2">
                        <textarea id="work_performed" wire:model="work_performed" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                    </div>
                    @error('work_performed') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-full">
                    <label for="equipment_usage" class="block text-sm font-medium leading-6 text-gray-900">Equipment Usage</label>
                    <div class="mt-2">
                        <textarea id="equipment_usage" wire:model="equipment_usage" rows="2" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                    </div>
                    @error('equipment_usage') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-full">
                    <label for="issues" class="block text-sm font-medium leading-6 text-gray-900">Issues</label>
                    <div class="mt-2">
                        <textarea id="issues" wire:model="issues" rows="2" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                    </div>
                    @error('issues') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-full">
                    <label for="safety_incidents" class="block text-sm font-medium leading-6 text-gray-900">Safety Incidents</label>
                    <div class="mt-2">
                        <textarea id="safety_incidents" wire:model="safety_incidents" rows="2" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                    </div>
                    @error('safety_incidents') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end px-4 py-4 border-t border-gray-900/10 gap-x-6 sm:px-8 bg-gray-50 rounded-b-xl">
            <a href="{{ route('daily_logs.index') }}" wire:navigate class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
            <button type="submit" class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
        </div>
    </form>
</div>
