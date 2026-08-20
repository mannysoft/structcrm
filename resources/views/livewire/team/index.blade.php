<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Domains\CompanyManagement\Actions\InviteStaffUserAction;
use App\Domains\CompanyManagement\Actions\UpdateStaffUserRoleAction;
use App\Models\User;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    private const ALL_ROLES = ['owner', 'administrator', 'project_manager', 'engineer', 'foreman', 'staff', 'accountant'];

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('required|string')]
    public string $role = 'staff';

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage_users'), 403);
    }

    public function with(): array
    {
        return [
            'users' => User::query()
                ->where('company_id', auth()->user()->company_id)
                ->where('type', User::TYPE_STAFF)
                ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')))
                ->orderBy('name')
                ->paginate(15),
            'roles' => $this->availableRoles(),
            'allRoles' => self::ALL_ROLES,
        ];
    }

    public function invite(InviteStaffUserAction $action): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage_users'), 403);

        $this->validate();

        abort_unless(in_array($this->role, $this->availableRoles(), true), 422);

        $action->execute(auth()->user()->company_id, $this->email, $this->role);

        $this->reset('email');
        $this->role = 'staff';

        session()->flash('status', 'Invitation sent.');
    }

    public function updateRole(UpdateStaffUserRoleAction $action, string $userId, string $role): void
    {
        $viewer = auth()->user();

        abort_unless($viewer->hasPermissionTo('manage_users'), 403);
        abort_unless(in_array($role, self::ALL_ROLES, true), 422);

        $user = User::query()
            ->where('company_id', $viewer->company_id)
            ->where('type', User::TYPE_STAFF)
            ->findOrFail($userId);

        abort_if($user->is($viewer), 403, 'You cannot change your own role.');

        if ($user->hasRole('owner') || $role === 'owner') {
            abort_unless($viewer->hasRole('owner'), 403, 'Only owners can assign or change the owner role.');
        }

        $action->execute($user, $role);

        session()->flash('status', 'Role updated.');
    }

    /**
     * @return list<string>
     */
    private function availableRoles(): array
    {
        if (auth()->user()->hasRole('owner')) {
            return self::ALL_ROLES;
        }

        return array_values(array_filter(self::ALL_ROLES, fn (string $role) => $role !== 'owner'));
    }
}; ?>

<div>
    <x-page-header title="Team" description="Invite team members and manage their roles." />

    <div class="mt-8 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <h2 class="text-base font-semibold leading-6 text-gray-900">Invite a team member</h2>
            <p class="mt-1 text-sm text-gray-500">They'll receive an email with a secure link to set their password.</p>

            <form wire:submit="invite" class="mt-4 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <x-input-label for="email" value="Email" />
                    <div class="mt-2">
                        <x-text-input type="email" wire:model="email" id="email" />
                    </div>
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div>
                    <x-input-label for="role" value="Role" />
                    <div class="mt-2">
                        <x-select wire:model="role" id="role">
                            @foreach ($roles as $roleName)
                                <option value="{{ $roleName }}">{{ ucwords(str_replace('_', ' ', $roleName)) }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <x-input-error :messages="$errors->get('role')" />
                </div>

                <div class="sm:col-span-3">
                    <x-button type="submit">Send invitation</x-button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-6 flex items-center gap-4">
        <div class="w-full sm:max-w-xs">
            <x-search-input placeholder="Search team…" />
        </div>
    </div>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Name</x-table.th>
                                <x-table.th>Email</x-table.th>
                                <x-table.th>Role</x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($users as $teamMember)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">
                                        {{ $teamMember->name }}
                                        @if ($teamMember->is(auth()->user()))
                                            <span class="ml-2 text-xs font-medium text-brand-700">(you)</span>
                                        @endif
                                    </x-table.td>
                                    <x-table.td>{{ $teamMember->email }}</x-table.td>
                                    <x-table.td>
                                        <select
                                            wire:change="updateRole('{{ $teamMember->id }}', $event.target.value)"
                                            @if ($teamMember->is(auth()->user()) || ($teamMember->hasRole('owner') && ! auth()->user()->hasRole('owner'))) disabled @endif
                                            class="block rounded-lg border-0 py-1.5 pl-2 pr-8 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6">
                                            @foreach ($allRoles as $roleName)
                                                <option value="{{ $roleName }}" @if ($teamMember->getRoleNames()->first() === $roleName) selected @endif>{{ ucwords(str_replace('_', ' ', $roleName)) }}</option>
                                            @endforeach
                                        </select>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <x-empty-state title="No team members yet." description="Invite your first team member above." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $users->links() }}
    </div>
</div>
