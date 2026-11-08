<?php

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\CompanyManagement\Services\GettingStartedService;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component {
    public function with(GettingStartedService $gettingStarted): array
    {
        $company = auth()->user()?->company;

        abort_unless($company instanceof Company, 403);

        $progress = $gettingStarted->progress($company);

        return [
            'progress' => $progress,
            'requires' => collect($progress->steps)->keyBy('key'),
        ];
    }

    public function dismiss(GettingStartedService $gettingStarted): void
    {
        $company = auth()->user()?->company;

        if ($company instanceof Company) {
            $gettingStarted->dismiss($company);

            session()->flash('status', 'Getting started checklist hidden from the dashboard.');
        }
    }

    public function restore(GettingStartedService $gettingStarted): void
    {
        $company = auth()->user()?->company;

        if ($company instanceof Company) {
            $gettingStarted->restore($company);

            session()->flash('status', 'Getting started checklist shown on the dashboard.');
        }
    }
}; ?>

<div>
    <x-page-header title="Getting Started" description="Finish these steps to unlock projects, contracts, BOQ and invoicing." />

    @if (session('getting_started_notice'))
        <div class="mt-6 rounded-xl bg-amber-50 p-4 ring-1 ring-amber-600/20">
            <div class="flex items-start gap-3">
                <svg class="size-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <p class="text-sm font-medium text-amber-800">{{ session('getting_started_notice') }}</p>
            </div>
        </div>
    @endif

    @php($nextStep = $progress->nextStep())

    <div class="mt-6 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-base font-semibold text-gray-900">
                    {{ $progress->isComplete() ? "You're all set" : 'Setup progress' }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $progress->completed() }} of {{ $progress->total() }} steps complete.
                    @if ($nextStep)
                        Next up: <span class="font-medium text-gray-900">{{ $nextStep->title }}</span>.
                    @endif
                </p>
            </div>

            <span class="text-2xl font-semibold tracking-tight {{ $progress->isComplete() ? 'text-emerald-600' : 'text-brand-600' }}">
                {{ $progress->percentage() }}%
            </span>
        </div>

        <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-gray-200">
            <div class="h-full rounded-full {{ $progress->isComplete() ? 'bg-emerald-500' : 'bg-brand-600' }} transition-all" style="width: {{ $progress->percentage() }}%"></div>
        </div>
    </div>

    @if ($progress->isComplete())
        <div class="mt-6 rounded-xl bg-emerald-50 p-5 ring-1 ring-emerald-600/20">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-sm font-semibold text-emerald-900">Your workspace is ready</h2>
                        <p class="mt-1 text-sm text-emerald-800">Every setup step is complete. Keep the momentum going by recording your next invoice.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <x-button :href="route('projects.index')" variant="secondary" size="sm">View projects</x-button>
                    <x-button :href="route('invoices.create')" size="sm">New invoice</x-button>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
        <div class="border-b border-gray-200 px-5 py-5">
            <h2 class="text-base font-semibold text-gray-900">Checklist</h2>
            <p class="mt-1 text-sm text-gray-500">Complete the steps in order — each one unlocks the next part of the workflow.</p>
        </div>

        <ol class="divide-y divide-gray-200">
            @foreach ($progress->steps as $step)
                <li class="flex flex-wrap items-start justify-between gap-4 px-5 py-5">
                    <div class="flex min-w-0 items-start gap-4">
                        <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full text-sm font-semibold {{ $step->isCompleted ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            @if ($step->isCompleted)
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            @else
                                {{ $loop->iteration }}
                            @endif
                        </span>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm font-semibold {{ $step->isCompleted ? 'text-gray-500' : 'text-gray-900' }}">{{ $step->title }}</h3>

                                @if ($step->isCompleted)
                                    <x-badge tone="green">Complete</x-badge>
                                @elseif ($nextStep && $nextStep->key === $step->key)
                                    <x-badge tone="brand">Next step</x-badge>
                                @else
                                    <x-badge tone="gray">Not started</x-badge>
                                @endif
                            </div>

                            <p class="mt-1 text-sm text-gray-500">{{ $step->description }}</p>

                            @if ($step->missingDetails !== [])
                                <p class="mt-2 text-xs font-medium text-amber-700">
                                    Still missing: {{ implode(', ', $step->missingDetails) }}.
                                </p>
                            @endif

                            @if ($step->requires && ! $step->isCompleted && isset($requires[$step->requires]) && ! $requires[$step->requires]->isCompleted)
                                <p class="mt-2 text-xs text-gray-500">
                                    Requires: <span class="font-medium text-gray-700">{{ $requires[$step->requires]->title }}</span>
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2 pl-12 sm:pl-0">
                        <x-button :href="$step->url" :variant="$step->isCompleted ? 'secondary' : 'primary'" size="sm">
                            {{ $step->isCompleted ? 'Open' : $step->actionLabel }}
                        </x-button>
                    </div>
                </li>
            @endforeach
        </ol>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 bg-gray-50 px-5 py-4">
            <p class="text-sm text-gray-500">
                @if ($progress->isDismissed)
                    The checklist is currently hidden from your dashboard.
                @else
                    The checklist is shown on your dashboard until every step is complete.
                @endif
            </p>

            @if ($progress->isDismissed)
                <x-button type="button" wire:click="restore" variant="secondary" size="sm">Show on dashboard</x-button>
            @else
                <x-button type="button" wire:click="dismiss" variant="secondary" size="sm">Hide from dashboard</x-button>
            @endif
        </div>
    </div>
</div>
