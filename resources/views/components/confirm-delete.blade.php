@props(['uuid', 'title' => 'Delete this record?', 'message' => 'This action cannot be undone.'])

<button
    type="button"
    x-data
    @click="$dispatch('open-modal', 'confirm-delete-{{ $uuid }}')"
    {{ $attributes->merge(['class' => 'text-sm font-medium text-red-600 hover:text-red-900']) }}
>
    {{ $slot }}
</button>

<div
    x-data="{ show: false }"
    x-on:open-modal.window="$event.detail === 'confirm-delete-{{ $uuid }}' ? show = true : null"
    x-on:close-modal.window="$event.detail === 'confirm-delete-{{ $uuid }}' ? show = false : null"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" x-show="show" x-on:click="show = false" x-transition.opacity></div>

    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center">
        <div
            class="w-full max-w-md rounded-2xl bg-white p-6 text-left shadow-xl transition-all"
            x-show="show"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
        >
            <div class="flex items-start gap-4">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-100">
                    <svg class="size-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $message }}</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-button type="button" variant="secondary" @click="show = false">{{ __('Cancel') }}</x-button>
                <x-button type="button" variant="danger" wire:click="delete('{{ $uuid }}')" @click="show = false">{{ __('Delete') }}</x-button>
            </div>
        </div>
    </div>
</div>
