<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Sales\Actions\InviteClientUserAction;
use App\Domains\Sales\Models\Client;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class InviteClientPortalUser implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Invite a contact at a client company to the external client portal by email. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Inviting a client user grants external portal access to project data.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'client' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $client = $this->findRecord(Client::class, $validated['client'], 'client', ['name', 'company_name']);
            $this->authorize('update', $client);

            $user = app(InviteClientUserAction::class)->execute($client, $validated['email']);

            return [
                'message' => "Portal invitation sent to {$user->email} for {$client->name}.",
                'client' => $this->clientSummary($client),
                'portal_user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
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
            'email' => $schema->string()->description('Portal user email address.')->required(),
        ];
    }
}
