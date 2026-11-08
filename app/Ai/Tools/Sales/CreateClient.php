<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Sales\DTOs\ClientData;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Services\ClientService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class CreateClient implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Create a client record with contact details. A client is required before a project can be created.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', Client::class);

            $client = app(ClientService::class)->create(
                new ClientData(
                    name: $validated['name'],
                    companyName: $validated['company_name'] ?? null,
                    email: $validated['email'] ?? null,
                    phone: $validated['phone'] ?? null,
                    address: $validated['address'] ?? null,
                    notes: $validated['notes'] ?? null,
                ),
                $this->companyId(),
            );

            return [
                'message' => 'Client created.',
                'client' => $this->clientSummary($client),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Primary contact name.')->required(),
            'company_name' => $schema->string()->description('Client company name.'),
            'email' => $schema->string()->description('Contact email.'),
            'phone' => $schema->string()->description('Contact phone.'),
            'address' => $schema->string()->description('Billing address.'),
            'notes' => $schema->string()->description('Optional notes.'),
        ];
    }
}
