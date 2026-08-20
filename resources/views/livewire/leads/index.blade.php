<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Domains\Sales\Actions\DeleteLeadAction;
use App\Domains\Sales\Models\Lead;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'leads' => Lead::query()
                ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('last_name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('phone', 'like', '%'.$this->search.'%')))
                ->latest()
                ->paginate(15),
        ];
    }

    public function delete(DeleteLeadAction $action, string $uuid): void
    {
        $lead = Lead::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($lead, 404);

        $this->authorize('delete', $lead);

        $action->execute($lead);

        session()->flash('status', 'Lead deleted.');
    }
}; ?>

<div>
    <x-page-header title="Leads" description="A list of all prospective clients and leads.">
        <x-slot name="actions">
            <x-button :href="route('leads.create')">Add lead</x-button>
        </x-slot>
    </x-page-header>

    <div class="mt-6 flex items-center gap-4">
        <div class="w-full sm:max-w-xs">
            <x-search-input placeholder="Search leads…" />
        </div>
    </div>
    
    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Name</x-table.th>
                                <x-table.th>Email</x-table.th>
                                <x-table.th>Phone</x-table.th>
                                <x-table.th>Status</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">View</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($leads as $lead)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $lead->first_name }} {{ $lead->last_name }}</x-table.td>
                                    <x-table.td>{{ $lead->email ?? 'N/A' }}</x-table.td>
                                    <x-table.td>{{ $lead->phone ?? 'N/A' }}</x-table.td>
                                    <x-table.td>
                                        <x-badge tone="sky">{{ ucfirst($lead->status->value) }}</x-badge>
                                    </x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <a href="{{ route('leads.edit', $lead) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                                        <x-confirm-delete :uuid="$lead->uuid" class="ml-4">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-empty-state title="No leads found." description="Add a lead to get started.">
                                            <x-slot name="action">
                                                <x-button :href="route('leads.create')">Add lead</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $leads->links() }}
    </div>
</div>
