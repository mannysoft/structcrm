<?php

declare(strict_types=1);

namespace App\Ai\Tools\Company;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListTeamMembers implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List staff users in the company with their roles, optionally filtered by a name/email search or by role.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorizePermission('manage_users');

            $search = trim((string) ($request['search'] ?? ''));
            $role = $request['role'] ?? null;

            $members = User::query()
                ->where('company_id', $this->companyId())
                ->where('type', User::TYPE_STAFF)
                ->when($search !== '', fn ($query) => $query->where(
                    fn ($builder) => $builder->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")
                ))
                ->when(filled($role), fn ($query) => $query->role((string) $role))
                ->orderBy('name')
                ->limit($this->limit($request['limit'] ?? null, 50, 200))
                ->get();

            return [
                'count' => $members->count(),
                'members' => $members->map(fn (User $user) => $this->teamMemberSummary($user))->all(),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Optional name or email fragment.'),
            'role' => $schema->string()->description('Optional role name filter.'),
            'limit' => $schema->integer()->description('Maximum members to return (default 50, max 200).'),
        ];
    }
}
