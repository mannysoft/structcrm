<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\Sales\Actions\UpdateLeadAction;
use App\Domains\Sales\DTOs\LeadData;
use App\Domains\Sales\Enums\LeadStatus;
use App\Domains\Sales\Models\Lead;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdateLead implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a lead: contact details, notes or pipeline status (new, contacted, qualified, lost, converted).';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'lead' => ['required', 'string'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $lead = $this->findRecord(Lead::class, $validated['lead'], 'lead', ['email']);
            $this->authorize('update', $lead);

            try {
                $status = filled($validated['status'] ?? null)
                    ? LeadStatus::from($validated['status'])
                    : $lead->status;
            } catch (Throwable) {
                throw new ToolFailure('Unsupported lead status. Use one of: '.implode(', ', array_column(LeadStatus::cases(), 'value')).'.');
            }

            $updated = app(UpdateLeadAction::class)->execute($lead, new LeadData(
                firstName: (string) ($validated['first_name'] ?? $lead->first_name),
                lastName: (string) ($validated['last_name'] ?? $lead->last_name),
                status: $status,
                email: $validated['email'] ?? $lead->email,
                phone: $validated['phone'] ?? $lead->phone,
                notes: $validated['notes'] ?? $lead->notes,
            ));

            return [
                'message' => 'Lead updated.',
                'lead' => $this->leadSummary($updated->fresh()),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'lead' => $schema->string()->description('Lead UUID or email.')->required(),
            'first_name' => $schema->string()->description('New first name.'),
            'last_name' => $schema->string()->description('New last name.'),
            'email' => $schema->string()->description('New email.'),
            'phone' => $schema->string()->description('New phone.'),
            'status' => $schema->string()->enum(array_column(LeadStatus::cases(), 'value'))->description('New pipeline status.'),
            'notes' => $schema->string()->description('New notes.'),
        ];
    }
}
