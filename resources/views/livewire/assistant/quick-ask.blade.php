<?php

use Livewire\Component;

new class extends Component {
    //
};

?>

<div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5">
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
                </svg>
            </span>
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Ask AI</h2>
                <p class="mt-1 text-xs text-gray-500">Open the assistant panel for instant answers about projects, billing and the team.</p>
            </div>
        </div>

        <x-button type="button" size="sm" x-on:click="$dispatch('open-assistant')" class="shrink-0">
            Open assistant
        </x-button>
    </div>

    <div class="mt-4 flex flex-wrap gap-2">
        @foreach ([
            'Which invoices are overdue?',
            'How is the business doing this month?',
            'Show me the least profitable projects.',
        ] as $suggestion)
            <button
                type="button"
                x-on:click="$dispatch('open-assistant'); $dispatch('ask-assistant', { prompt: @js($suggestion) })"
                class="rounded-full bg-gray-50 px-3 py-1.5 text-xs text-gray-600 ring-1 ring-gray-200 transition hover:bg-gray-100 hover:text-gray-900"
            >
                {{ $suggestion }}
            </button>
        @endforeach
    </div>

    <p class="mt-3 text-xs text-gray-400">
        Tip: press <kbd class="rounded border border-gray-200 bg-gray-50 px-1 py-0.5 font-sans text-[11px] text-gray-500">Ctrl/⌘ + I</kbd> anywhere, or
        <a href="{{ route('assistant.index') }}" wire:navigate class="font-medium text-brand-600 hover:text-brand-500">open the full page</a>.
    </p>
</div>
