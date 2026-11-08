<?php

use App\Ai\Agents\CrmAssistant;
use App\Models\User;
use Illuminate\Support\HtmlString;
use Laravel\Ai\Approvals\Decisions;
use Laravel\Ai\Responses\StreamedAgentResponse;
use Laravel\Ai\Streaming\Events\TextDelta;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public string $prompt = '';

    public ?string $conversationId = null;

    public bool $showHistory = false;

    public bool $compact = false;

    /** @var array<int, array{role: string, content: string}> */
    public array $messages = [];

    /** @var array<int, array{id: string, tool: string, arguments: array<string, mixed>, reason: ?string}> */
    public array $pendingApprovals = [];

    /** @var array<string, bool> */
    public array $decisions = [];

    public ?string $errorMessage = null;

    public function mount(bool $showHistory = false, bool $compact = false): void
    {
        abort_if(auth()->user()->isClient(), 403);

        $this->showHistory = $showHistory;
        $this->compact = $compact;

        $this->conversationId = auth()->user()->conversations()->latest('updated_at')->value('id');

        if ($this->conversationId !== null) {
            $this->loadMessages($this->conversationId);
        }
    }

    /**
     * Prefill the composer when another surface asks the assistant a question.
     */
    #[On('ask-assistant')]
    public function prefillPrompt(string $prompt = ''): void
    {
        $this->prompt = trim($prompt);
        $this->errorMessage = null;
    }

    public function send(): void
    {
        $validated = $this->validate([
            'prompt' => ['required', 'string', 'max:4000'],
        ]);

        $message = trim($validated['prompt']);

        $this->prompt = '';
        $this->errorMessage = null;

        $this->messages[] = ['role' => 'user', 'content' => $message];

        $this->streamUserBubble($message);
        $this->streamAssistantStart();

        $this->runTurn($message, null);
    }

    public function decide(string $approvalId, bool $approved): void
    {
        $this->decisions[$approvalId] = $approved;
    }

    public function resume(): void
    {
        if ($this->conversationId === null || $this->pendingApprovals === []) {
            return;
        }

        $decisions = [];

        foreach ($this->pendingApprovals as $approval) {
            $decisions[$approval['id']] = $this->decisions[$approval['id']] ?? false;
        }

        $this->pendingApprovals = [];
        $this->errorMessage = null;

        $this->streamAssistantStart();

        $this->runTurn(null, $decisions);
    }

    public function newConversation(): void
    {
        $this->conversationId = null;
        $this->messages = [];
        $this->pendingApprovals = [];
        $this->decisions = [];
        $this->errorMessage = null;
        $this->prompt = '';
    }

    public function loadConversation(string $conversationId): void
    {
        $belongsToUser = auth()->user()->conversations()->whereKey($conversationId)->exists();

        if (! $belongsToUser) {
            return;
        }

        $this->conversationId = $conversationId;
        $this->pendingApprovals = [];
        $this->decisions = [];
        $this->errorMessage = null;

        $this->loadMessages($conversationId);
    }

    /**
     * @param  array<string, bool>|null  $decisions
     */
    private function runTurn(?string $message, ?array $decisions): void
    {
        /** @var User $user */
        $user = auth()->user();

        $agent = new CrmAssistant($user);

        if ($this->conversationId !== null) {
            $agent->continue($this->conversationId, as: $user);
        } else {
            $agent->forUser($user);
        }

        $final = null;
        $text = '';

        try {
            $response = $decisions !== null
                ? $agent->stream(Decisions::from($decisions))
                : $agent->stream((string) $message);

            $response->then(function (StreamedAgentResponse $result) use (&$final): void {
                $final = $result;
            });

            foreach ($response as $event) {
                if ($event instanceof TextDelta) {
                    $text .= $event->delta;

                    $this->pushStream(e($event->delta));
                }
            }

            $this->conversationId = $response->conversationId ?? $this->conversationId;

            // The SDK separates multi-step narration with blank lines; prefer its
            // combined text over the raw concatenated deltas.
            $text = (string) $response->text !== '' ? (string) $response->text : $text;
        } catch (Throwable $exception) {
            report($exception);

            $this->errorMessage = 'The assistant could not complete that request. '.$exception->getMessage();
        }

        $this->streamAssistantEnd();
        $this->pushStream('', replace: true);

        if ($text !== '') {
            $this->messages[] = ['role' => 'assistant', 'content' => $text];
        }

        $this->pendingApprovals = $final instanceof StreamedAgentResponse
            ? $final->pendingApprovals->map(fn ($approval) => $approval->toArray())->all()
            : [];

        $this->decisions = [];
    }

    private function loadMessages(string $conversationId): void
    {
        $conversation = auth()->user()->conversations()->with('messages')->whereKey($conversationId)->first();

        $this->messages = $conversation === null
            ? []
            : $conversation->messages
                ->whereIn('role', ['user', 'assistant'])
                ->sortBy('created_at')
                ->map(fn ($message) => [
                    'role' => $message->role,
                    'content' => (string) $message->content,
                ])
                ->values()
                ->all();
    }

    private function streamUserBubble(string $message): void
    {
        $html = '<div class="flex justify-end"><div class="max-w-[85%] whitespace-pre-wrap rounded-2xl bg-brand-600 px-4 py-3 text-sm text-white">'
            .e($message).'</div></div>';

        $this->pushStream($html);
    }

    private function streamAssistantStart(): void
    {
        $this->pushStream('<div class="flex"><div class="max-w-[85%] whitespace-pre-wrap rounded-2xl bg-white px-4 py-3 text-sm text-gray-800 ring-1 ring-gray-200">');
    }

    private function streamAssistantEnd(): void
    {
        $this->pushStream('</div></div>');
    }

    private function pushStream(string $content, bool $replace = false): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $this->stream(content: $content, name: 'assistant-live', replace: $replace);
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

    /**
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'conversations' => $this->showHistory
                ? auth()->user()->conversations()->latest('updated_at')->limit(20)->get(['id', 'title', 'updated_at'])
                : collect(),
            'suggestions' => [
                'How is the business doing this month?',
                'Which invoices are overdue?',
                'Show me the least profitable projects.',
                'What is outstanding on my active projects?',
            ],
        ];
    }
};

?>

<div @class(['flex min-h-0 flex-1 gap-6', 'h-full flex-col' => ! $showHistory, 'h-full flex-col lg:flex-row' => $showHistory])>
    @if ($showHistory)
        <aside class="hidden w-64 shrink-0 flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 lg:flex">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
                <h2 class="text-sm font-semibold text-gray-900">Conversations</h2>
                <x-button variant="link" size="sm" type="button" wire:click="newConversation">New</x-button>
            </div>
            <nav class="flex-1 space-y-1 overflow-y-auto p-2">
                @forelse ($conversations as $conversation)
                    <button
                        type="button"
                        wire:key="conversation-{{ $conversation->id }}"
                        wire:click="loadConversation('{{ $conversation->id }}')"
                        class="block w-full truncate rounded-lg px-3 py-2 text-left text-sm {{ $conversationId === $conversation->id ? 'bg-brand-50 font-medium text-brand-700' : 'text-gray-600 hover:bg-gray-50' }}"
                    >
                        {{ $conversation->title ?: 'Untitled conversation' }}
                    </button>
                @empty
                    <p class="px-3 py-2 text-sm text-gray-500">No conversations yet.</p>
                @endforelse
            </nav>
        </aside>
    @endif

    <section @class([
        'flex min-h-0 flex-1 flex-col overflow-hidden bg-gray-50',
        'rounded-xl shadow-sm ring-1 ring-gray-900/5' => ! $compact,
    ])>
        @unless ($compact)
            <header class="flex items-center justify-between border-b border-gray-200 bg-white px-4 py-3">
                <div>
                    <h1 class="text-sm font-semibold text-gray-900">AI Assistant</h1>
                    <p class="text-xs text-gray-500">Ask about projects, billing, sales and site operations.</p>
                </div>
                <x-button variant="secondary" size="sm" type="button" wire:click="newConversation" class="lg:hidden">New chat</x-button>
            </header>
        @else
            <div class="flex items-center justify-between border-b border-gray-200 bg-white px-4 py-2">
                <p class="text-xs text-gray-500">Ask about projects, billing, sales or the team.</p>
                <x-button variant="link" size="sm" type="button" wire:click="newConversation">New chat</x-button>
            </div>
        @endunless

        <div @class(['flex-1 space-y-4 overflow-y-auto px-4', 'py-5' => ! $compact, 'py-4' => $compact])>
            @if ($messages === [])
                <div class="mx-auto max-w-lg text-center">
                    <p class="text-sm font-medium text-gray-900">What would you like to know?</p>
                    <div @class(['mt-4 grid gap-2', 'sm:grid-cols-2' => ! $compact])>
                        @foreach ($suggestions as $suggestion)
                            <button
                                type="button"
                                wire:click="$set('prompt', @js($suggestion))"
                                class="rounded-lg bg-white px-3 py-2 text-left text-xs text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50"
                            >
                                {{ $suggestion }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @foreach ($messages as $index => $chatMessage)
                @if ($chatMessage['role'] === 'user')
                    <div wire:key="message-user-{{ $index }}" class="flex justify-end">
                        <div class="max-w-[85%] whitespace-pre-wrap rounded-2xl bg-brand-600 px-4 py-3 text-sm text-white">{{ $chatMessage['content'] }}</div>
                    </div>
                @else
                    <div wire:key="message-assistant-{{ $index }}" class="flex">
                        <div class="prose prose-sm max-w-[85%] rounded-2xl bg-white px-4 py-3 text-sm text-gray-800 ring-1 ring-gray-200 prose-pre:overflow-x-auto">
                            {!! $this->markdown($chatMessage['content']) !!}
                        </div>
                    </div>
                @endif
            @endforeach

            <div wire:stream="assistant-live" class="space-y-4 empty:hidden"></div>

            @if ($pendingApprovals !== [])
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <p class="text-sm font-semibold text-amber-900">Approval required</p>
                    <p class="mt-1 text-xs text-amber-800">
                        The assistant wants to run {{ count($pendingApprovals) }} action(s). Nothing has been changed yet.
                    </p>

                    <ul class="mt-3 space-y-3">
                        @foreach ($pendingApprovals as $approval)
                            <li wire:key="approval-{{ $approval['id'] }}" class="rounded-lg bg-white p-3 ring-1 ring-amber-200">
                                <p class="text-sm font-medium text-gray-900">{{ $approval['tool'] }}</p>
                                @if ($approval['reason'])
                                    <p class="mt-1 text-xs text-gray-600">{{ $approval['reason'] }}</p>
                                @endif
                                <pre class="mt-2 max-h-32 overflow-auto rounded bg-gray-50 p-2 text-xs text-gray-700">{{ json_encode($approval['arguments'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>

                                <div class="mt-2 flex items-center gap-2">
                                    <x-button size="sm" type="button" wire:click="decide('{{ $approval['id'] }}', true)">Approve</x-button>
                                    <x-button variant="secondary" size="sm" type="button" wire:click="decide('{{ $approval['id'] }}', false)">Reject</x-button>

                                    @if (array_key_exists($approval['id'], $decisions))
                                        <span class="text-xs font-medium {{ $decisions[$approval['id']] ? 'text-emerald-600' : 'text-red-600' }}">
                                            {{ $decisions[$approval['id']] ? 'Approved' : 'Rejected' }}
                                        </span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <x-button size="sm" type="button" wire:click="resume" class="mt-3">Continue with decisions</x-button>
                </div>
            @endif

            @if ($errorMessage)
                <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-red-200">
                    {{ $errorMessage }}
                </div>
            @endif
        </div>

        <form wire:submit="send" class="border-t border-gray-200 bg-white p-3">
            <div class="flex items-end gap-2">
                <x-textarea
                    wire:model="prompt"
                    rows="2"
                    placeholder="Ask about a project, invoice, team or campaign…"
                    class="resize-none"
                ></x-textarea>

                <x-button type="submit" wire:target="send,resume">
                    <span wire:loading.remove wire:target="send,resume">Send</span>
                    <span wire:loading wire:target="send,resume">Thinking…</span>
                </x-button>
            </div>

            @error('prompt')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </form>
    </section>
</div>
