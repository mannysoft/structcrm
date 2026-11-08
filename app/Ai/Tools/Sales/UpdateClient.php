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

final class UpdateClient implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update a client record. Only supplied fields change.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'client' => ['required', 'string'],
            'name' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $client = $this->findRecord(Client::class, $validated['client'], 'client', ['name', 'company_name']);
            $this->authorize('update', $client);

            $updated = app(ClientService::class)->update($client, new ClientData(
                name: (string) ($validated['name'] ?? $client->name),
                companyName: $validated['company_name'] ?? $client->company_name,
                email: $validated['email'] ?? $client->email,
                phone: $validated['phone'] ?? $client->phone,
                address: $validated['address'] ?? $client->address,
                notes: $validated['notes'] ?? $client->notes,
            ));

            return [
                'message' => 'Client updated.',
                'client' => $this->clientSummary($updated),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'client' => $schema->string()->description('Client UUID, name or company name.')->required(),
            'name' => $schema->string()->description('New contact name.'),
            'company_name' => $schema->string()->description('New company name.'),
            'email' => $schema->string()->description('New email.'),
            'phone' => $schema->string()->description('New phone.'),
            'address' => $schema->string()->description('New address.'),
            'notes' => $schema->string()->description('New notes.'),
        ];
    }
}
