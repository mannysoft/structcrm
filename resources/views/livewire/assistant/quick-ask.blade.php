<?php

use App\Ai\Agents\CrmAssistant;
use App\Models\User;
use Illuminate\Support\HtmlString;
use Livewire\Component;

new class extends Component {
    public string $prompt = '';

    public ?string $answer = null;

    public ?string $errorMessage = null;

    public ?string $conversationId = null;

    public bool $needsApproval = false;

    public function ask(): void
    {
        $validated = $this->validate([
            'prompt' => ['required', 'string', 'max:1000'],
        ]);

        $this->answer = null;
        $this->errorMessage = null;
        $this->needsApproval = false;

        /** @var User $user */
        $user = auth()->user();

        try {
            $response = (new CrmAssistant($user))
                ->forUser($user)
                ->prompt(trim($validated['prompt']));

            $this->conversationId = $response->conversationId;
            $this->answer = $response->text;
            $this->needsApproval = $response->hasPendingApprovals();
        } catch (Throwable $exception) {
            report($exception);

            $this->errorMessage = 'The assistant could not answer right now. '.$exception->getMessage();
        }

        $this->prompt = '';
    }

    public function markdown(string $content): HtmlString
    {
        return new HtmlString(
            (string) str($content)->markdown([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ])
        );
    }
};

?>

<div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold text-gray-900">Ask AI</h2>
            <p class="mt-1 text-xs text-gray-500">Get an instant answer about projects, billing, sales or the team.</p>
        </div>
        <a href="{{ route('assistant.index') }}" wire:navigate class="text-xs font-medium text-brand-600 hover:text-brand-700">Open assistant</a>
    </div>

    <form wire:submit="ask" class="mt-4 flex items-end gap-2">
        <x-text-input
            type="text"
            wire:model="prompt"
            placeholder="e.g. Which invoices are overdue?"
        />
        <x-button type="submit" wire:target="ask" class="shrink-0">
            <span wire:loading.remove wire:target="ask">Ask</span>
            <span wire:loading wire:target="ask">…</span>
        </x-button>
    </form>

    @error('prompt')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror

    @if ($answer)
        <div class="prose prose-sm mt-4 max-w-none rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-800 prose-pre:overflow-x-auto">
            {!! $this->markdown($answer) !!}
        </div>
    @endif

    @if ($needsApproval)
        <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800 ring-1 ring-amber-200">
            This request needs approval. <a href="{{ route('assistant.index') }}" wire:navigate class="font-semibold underline">Review it in the assistant</a>.
        </p>
    @endif

    @if ($errorMessage)
        <p class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700 ring-1 ring-red-200">{{ $errorMessage }}</p>
    @endif
</div>
