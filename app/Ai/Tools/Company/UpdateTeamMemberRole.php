<?php

declare(strict_types=1);

namespace App\Ai\Tools\Company;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\CompanyManagement\Actions\UpdateStaffUserRoleAction;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Spatie\Permission\Models\Role;
use Stringable;

final class UpdateTeamMemberRole implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Change the role of an existing staff member, identified by email or user id. Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Changing a role alters what the team member can access.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'member' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:64'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorizePermission('manage_users');

            $member = User::query()
                ->where('company_id', $this->companyId())
                ->where('type', User::TYPE_STAFF)
                ->where(function ($query) use ($validated) {
                    $query->where('email', $validated['member']);

                    if (ctype_digit($validated['member'])) {
                        $query->orWhere('id', (int) $validated['member']);
                    }
                })
                ->first();

            if (! $member instanceof User) {
                throw new ToolFailure("No staff member matching [{$validated['member']}] was found.");
            }

            $role = Role::query()->where('name', $validated['role'])->first();

            if (! $role instanceof Role) {
                throw new ToolFailure(
                    'Unknown role ['.$validated['role'].']. Available roles: '.Role::query()->pluck('name')->implode(', ').'.'
                );
            }

            $updated = app(UpdateStaffUserRoleAction::class)->execute($member, $role->name);

            return [
                'message' => "{$updated->name} is now a {$role->name}.",
                'member' => $this->teamMemberSummary($updated->refresh()),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'member' => $schema->string()->description('Staff member email address or user id.')->required(),
            'role' => $schema->string()->description('New role name.')->required(),
        ];
    }
}
