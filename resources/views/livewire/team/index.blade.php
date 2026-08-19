<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Domains\CompanyManagement\Actions\InviteStaffUserAction;
use App\Domains\CompanyManagement\Actions\UpdateStaffUserRoleAction;
use App\Models\User;

new #[Layout('layouts.app')] class extends Component {
    private const ALL_ROLES = ['owner', 'administrator', 'project_manager', 'engineer', 'foreman', 'staff', 'accountant'];

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('required|string')]
    public string $role = 'staff';

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
                ->orderBy('name')
                ->get(),
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
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">Team</h1>
            <p class="mt-2 text-sm text-gray-700">Invite team members and manage their roles.</p>
        </div>
    </div>

    <div class="mt-8 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <h2 class="text-base font-semibold leading-6 text-gray-900">Invite a team member</h2>
            <p class="mt-1 text-sm text-gray-500">They'll receive an email with a secure link to set their password.</p>

            <form wire:submit="invite" class="mt-4 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email</label>
                    <div class="mt-2">
                        <input type="email" wire:model="email" id="email" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium leading-6 text-gray-900">Role</label>
                    <div class="mt-2">
                        <select wire:model="role" id="role" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            @foreach ($roles as $roleName)
                                <option value="{{ $roleName }}">{{ ucwords(str_replace('_', ' ', $roleName)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('role') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <button type="submit" class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Send invitation</button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Email</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Role</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($users as $teamMember)
                                <tr>
                                    <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-6">
                                        {{ $teamMember->name }}
                                        @if ($teamMember->is(auth()->user()))
                                            <span class="ml-2 text-xs font-medium text-indigo-700">(you)</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $teamMember->email }}</td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        <select
                                            wire:change="updateRole('{{ $teamMember->id }}', $event.target.value)"
                                            @if ($teamMember->is(auth()->user()) || ($teamMember->hasRole('owner') && ! auth()->user()->hasRole('owner'))) disabled @endif
                                            class="block rounded-md border-0 py-1.5 pl-2 pr-8 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                            @foreach ($allRoles as $roleName)
                                                <option value="{{ $roleName }}" @if ($teamMember->getRoleNames()->first() === $roleName) selected @endif>{{ ucwords(str_replace('_', ' ', $roleName)) }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-8 text-sm text-center text-gray-500">
                                        No team members yet. Invite your first team member above.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
