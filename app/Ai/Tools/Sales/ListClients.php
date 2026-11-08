<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Services\ClientService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListClients implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List or search clients by name, company name, email or phone. Returns each client UUID for use in other tools.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', Client::class);

            $clients = app(ClientService::class)->paginate(
                $this->companyId(),
                trim((string) ($request['search'] ?? '')),
                $this->limit($request['limit'] ?? null),
            );

            return [
                'count' => $clients->total(),
                'clients' => collect($clients->items())
                    ->map(fn (Client $client) => $this->clientSummary($client))
                    ->all(),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Optional name, company name, email or phone fragment.'),
            'limit' => $schema->integer()->description('Maximum clients to return (default 25, max 100).'),
        ];
    }
}
