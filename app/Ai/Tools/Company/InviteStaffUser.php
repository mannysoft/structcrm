<?php

declare(strict_types=1);

namespace App\Ai\Tools\Company;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\CompanyManagement\Actions\InviteStaffUserAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Spatie\Permission\Models\Role;
use Stringable;

final class InviteStaffUser implements Approvable, Tool
{
    use FormatsCompanyRecords, InteractsWithApprovals, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Invite a staff user by email and assign a role (owner, administrator, project_manager, engineer, foreman, staff, accountant, quantity_surveyor). Requires human approval before it runs.';
    }

    /**
     * Determine whether approval is required.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required('Inviting a staff member grants access to company data.');
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', 'max:64'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorizePermission('manage_users');

            $role = Role::query()->where('name', $validated['role'])->first();

            if (! $role instanceof Role) {
                throw new ToolFailure(
                    'Unknown role ['.$validated['role'].']. Available roles: '.Role::query()->pluck('name')->implode(', ').'.'
                );
            }

            $user = app(InviteStaffUserAction::class)->execute(
                $this->companyId(),
                $validated['email'],
                $role->name,
            );

            return [
                'message' => "Invitation sent to {$user->email} as {$role->name}.",
                'member' => $this->teamMemberSummary($user->refresh()),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'email' => $schema->string()->description('Staff member email address.')->required(),
            'role' => $schema->string()->description('Role to assign.')->required(),
        ];
    }
}
