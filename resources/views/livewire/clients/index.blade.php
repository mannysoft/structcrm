<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\Sales\Services\ClientService;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function with(ClientService $service): array
    {
        return [
            'clients' => $service->paginate(auth()->user()->company_id, $this->search),
        ];
    }

    public function delete(ClientService $service, string $uuid): void
    {
        $client = $service->find($uuid, auth()->user()->company_id);

        abort_unless($client, 404);

        $this->authorize('delete', $client);

        $service->delete($client);

        session()->flash('status', 'Client deleted.');
    }
}; ?>

<div>
    <x-page-header title="Clients" description="A list of all clients in your company.">
        <x-slot name="actions">
            <x-button :href="route('clients.create')">Add client</x-button>
        </x-slot>
    </x-page-header>

    <div class="mt-6 flex items-center gap-4">
        <div class="w-full sm:max-w-xs">
            <x-search-input placeholder="Search clients…" />
        </div>
    </div>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Name</x-table.th>
                                <x-table.th>Company</x-table.th>
                                <x-table.th>Email</x-table.th>
                                <x-table.th>Phone</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($clients as $client)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $client->name }}</x-table.td>
                                    <x-table.td>{{ $client->company_name ?? 'N/A' }}</x-table.td>
                                    <x-table.td>{{ $client->email ?? 'N/A' }}</x-table.td>
                                    <x-table.td>{{ $client->phone ?? 'N/A' }}</x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <a href="{{ route('clients.edit', $client) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                                        <x-confirm-delete :uuid="$client->uuid" class="ml-4">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-empty-state title="No clients found." description="Add a client to get started.">
                                            <x-slot name="action">
                                                <x-button :href="route('clients.create')">Add client</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $clients->links() }}
    </div>
</div>
