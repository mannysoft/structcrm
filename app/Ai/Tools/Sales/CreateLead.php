<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\Sales\Actions\CreateLeadAction;
use App\Domains\Sales\DTOs\LeadData;
use App\Domains\Sales\Enums\LeadStatus;
use App\Domains\Sales\Models\Lead;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class CreateLead implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Create a sales lead with contact details and an initial status (defaults to new).';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', Lead::class);

            try {
                $status = filled($validated['status'] ?? null)
                    ? LeadStatus::from($validated['status'])
                    : LeadStatus::New;
            } catch (Throwable) {
                throw new ToolFailure('Unsupported lead status. Use one of: '.implode(', ', array_column(LeadStatus::cases(), 'value')).'.');
            }

            $lead = app(CreateLeadAction::class)->execute(
                new LeadData(
                    firstName: $validated['first_name'],
                    lastName: $validated['last_name'],
                    status: $status,
                    email: $validated['email'] ?? null,
                    phone: $validated['phone'] ?? null,
                    notes: $validated['notes'] ?? null,
                ),
                $this->companyId(),
            );

            return [
                'message' => 'Lead created.',
                'lead' => $this->leadSummary($lead),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'first_name' => $schema->string()->description('Lead first name.')->required(),
            'last_name' => $schema->string()->description('Lead last name.')->required(),
            'email' => $schema->string()->description('Contact email.'),
            'phone' => $schema->string()->description('Contact phone.'),
            'status' => $schema->string()->enum(array_column(LeadStatus::cases(), 'value'))->description('Defaults to new.'),
            'notes' => $schema->string()->description('Optional notes.'),
        ];
    }
}
