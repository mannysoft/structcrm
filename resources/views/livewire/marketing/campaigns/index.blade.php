<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Domains\Marketing\Actions\DeleteMarketingCampaignAction;
use App\Domains\Marketing\Models\MarketingCampaign;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', MarketingCampaign::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'campaigns' => MarketingCampaign::query()
                ->withCount('steps')
                ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')))
                ->latest()
                ->paginate(15),
        ];
    }

    public function delete(DeleteMarketingCampaignAction $action, string $uuid): void
    {
        $campaign = MarketingCampaign::where('uuid', $uuid)->first();

        abort_unless($campaign, 404);

        $this->authorize('delete', $campaign);

        $action->execute($campaign);

        session()->flash('status', 'Campaign deleted.');
    }
}; ?>

<div>
    <x-page-header title="Marketing Campaigns" description="Email sequences sent to users who opted in at signup.">
        <x-slot name="actions">
            <x-button :href="route('marketing.campaigns.create')">New campaign</x-button>
        </x-slot>
    </x-page-header>

    <div class="mt-6 flex items-center gap-4">
        <div class="w-full sm:max-w-xs">
            <x-search-input placeholder="Search campaigns…" />
        </div>
    </div>

    <x-table>
        <thead class="bg-gray-50">
            <tr>
                <x-table.th>Name</x-table.th>
                <x-table.th>Emails</x-table.th>
                <x-table.th>Status</x-table.th>
                <x-table.th>
                    <span class="sr-only">Actions</span>
                </x-table.th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse ($campaigns as $campaign)
                <tr>
                    <x-table.td class="font-medium text-gray-900">{{ $campaign->name }}</x-table.td>
                    <x-table.td>{{ $campaign->steps_count }}</x-table.td>
                    <x-table.td>
                        @if ($campaign->is_active)
                            <x-badge tone="green">Active</x-badge>
                        @else
                            <x-badge>Inactive</x-badge>
                        @endif
                    </x-table.td>
                    <x-table.td class="text-right font-medium">
                        <a href="{{ route('marketing.campaigns.edit', $campaign) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                        <x-confirm-delete :uuid="$campaign->uuid" class="ml-4">Delete</x-confirm-delete>
                    </x-table.td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">
                        <x-empty-state title="No campaigns found." description="Create a campaign to start nurturing new signups.">
                            <x-slot name="action">
                                <x-button :href="route('marketing.campaigns.create')">New campaign</x-button>
                            </x-slot>
                        </x-empty-state>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $campaigns->links() }}
    </div>
</div>
