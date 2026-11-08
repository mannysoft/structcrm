<?php

use Livewire\Component;

new class extends Component {
    //
};

?>

<div
    x-data="{
        open: false,
        show() {
            this.open = true;

            setTimeout(() => this.$refs.panel?.querySelector('textarea')?.focus(), 60);
        },
        hide() {
            this.open = false;
        },
        toggle() {
            this.open ? this.hide() : this.show();
        },
    }"
    x-on:open-assistant.window="show()"
    x-on:close-assistant.window="hide()"
    x-on:keydown.escape.window="hide()"
    x-on:keydown.window.prevent.cmd.i="toggle()"
    x-on:keydown.window.prevent.ctrl.i="toggle()"
    x-effect="document.body.classList.toggle('overflow-hidden', open)"
>
    <button
        type="button"
        x-show="! open"
        x-on:click="show()"
        class="fixed bottom-6 right-6 z-40 inline-flex size-14 items-center justify-center rounded-full bg-brand-600 text-white shadow-lg ring-1 ring-black/5 transition hover:bg-brand-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
        aria-label="Open AI assistant"
        title="AI Assistant (Ctrl/⌘ + I)"
    >
        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
        </svg>
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click="hide()"
        class="fixed inset-0 z-40 bg-gray-900/50"
        aria-hidden="true"
    ></div>

    <aside
        x-ref="panel"
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-200 transform"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-50 flex w-full max-w-xl flex-col border-l border-gray-200 bg-gray-50 shadow-2xl"
        role="dialog"
        aria-modal="true"
        aria-label="AI Assistant"
    >
        <header class="flex shrink-0 items-center justify-between border-b border-gray-200 bg-white px-4 py-3">
            <div class="flex items-center gap-3">
                <span class="flex size-9 items-center justify-center rounded-xl bg-brand-600 text-white">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">AI Assistant</h2>
                    <p class="text-xs text-gray-500">Your CRM copilot</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('assistant.index') }}" wire:navigate class="text-xs font-medium text-brand-600 hover:text-brand-500">
                    Full page
                </a>

                <button
                    type="button"
                    x-on:click="hide()"
                    class="rounded-lg p-1.5 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700"
                    aria-label="Close AI assistant"
                >
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </header>

        <div class="min-h-0 flex-1">
            <livewire:assistant.chat :compact="true" />
        </div>
    </aside>
</div>
