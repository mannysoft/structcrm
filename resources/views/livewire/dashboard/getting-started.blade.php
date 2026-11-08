<?php

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\CompanyManagement\Services\GettingStartedService;
use Livewire\Component;

new class extends Component {
    public function with(GettingStartedService $gettingStarted): array
    {
        $company = auth()->user()?->company;

        return [
            'progress' => $company instanceof Company ? $gettingStarted->progress($company) : null,
        ];
    }

    public function dismiss(GettingStartedService $gettingStarted): void
    {
        $company = auth()->user()?->company;

        if ($company instanceof Company) {
            $gettingStarted->dismiss($company);
        }
    }
}; ?>

@php($showChecklist = $progress && ! $progress->isComplete() && ! $progress->isDismissed)
@php($nextStep = $showChecklist ? $progress->nextStep() : null)

<div @class(['mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5' => $showChecklist])>
    @if ($showChecklist)
        <div class="flex flex-wrap items-start justify-between gap-4 px-5 py-5">
            <div>
                <h2 class="text-base font-semibold text-gray-900">Getting started</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $progress->completed() }} of {{ $progress->total() }} steps complete. Finish the checklist to unlock the full workflow.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('getting_started.index') }}" wire:navigate class="text-sm font-semibold text-brand-600 hover:text-brand-500">View full checklist</a>
                <button type="button" wire:click="dismiss" class="text-sm font-medium text-gray-500 hover:text-gray-700">
                    Dismiss
                </button>
            </div>
        </div>

        <div class="px-5">
            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                <div class="h-full rounded-full bg-brand-600 transition-all" style="width: {{ $progress->percentage() }}%"></div>
            </div>
        </div>

        <ol class="mt-5 grid grid-cols-1 gap-x-8 gap-y-4 border-t border-gray-200 px-5 py-5 sm:grid-cols-2">
            @foreach ($progress->steps as $step)
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full text-xs font-semibold {{ $step->isCompleted ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                        @if ($step->isCompleted)
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        @else
                            {{ $loop->iteration }}
                        @endif
                    </span>

                    <div class="min-w-0">
                        <p class="text-sm font-medium {{ $step->isCompleted ? 'text-gray-400' : 'text-gray-900' }}">
                            {{ $step->title }}
                        </p>

                        @if ($step->isCompleted)
                            <p class="text-xs text-emerald-600">Complete</p>
                        @elseif ($nextStep && $nextStep->key === $step->key)
                            <a href="{{ $step->url }}" wire:navigate class="text-xs font-semibold text-brand-600 hover:text-brand-500">
                                {{ $step->actionLabel }} &rarr;
                            </a>
                        @else
                            <p class="text-xs text-gray-500">Not started</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</div>
