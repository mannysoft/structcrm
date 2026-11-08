<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ai;

use App\Ai\Agents\CrmAssistant;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Ai\Approvals\Decisions;

final class AssistantController extends Controller
{
    /**
     * Prompt the CRM assistant, or resume it with tool approval decisions.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null || $user->isClient(), 403, 'The assistant is only available to staff users.');

        $validated = $request->validate([
            'prompt' => ['nullable', 'string', 'max:4000', 'required_without:decisions'],
            'conversation_id' => ['nullable', 'string', 'max:64', 'required_with:decisions'],
            'decisions' => ['nullable', 'array'],
            'decisions.*' => ['boolean'],
        ]);

        $conversationId = $validated['conversation_id'] ?? null;
        $agent = new CrmAssistant($user);

        if (filled($conversationId)) {
            abort_unless(
                $user->conversations()->whereKey($conversationId)->exists(),
                404,
                'Conversation not found.',
            );

            $agent->continue($conversationId, as: $user);
        } else {
            $agent->forUser($user);
        }

        $decisions = $validated['decisions'] ?? [];

        $response = $decisions === []
            ? $agent->prompt((string) ($validated['prompt'] ?? ''))
            : $agent->prompt(Decisions::from($decisions));

        return response()->json([
            'conversation_id' => $response->conversationId,
            'message' => $response->text,
            'pending_approvals' => $response->pendingApprovals
                ->map(fn ($approval) => $approval->toArray())
                ->values()
                ->all(),
            'usage' => $response->usage->toArray(),
        ]);
    }
}
