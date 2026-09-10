<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\Finance\Actions\DeleteCostCodeAction;
use App\Domains\Finance\Models\CostCode;

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
            'costCodes' => CostCode::query()
                ->with('parent')
                ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('code', 'like', '%'.$this->search.'%')
                    ->orWhere('name', 'like', '%'.$this->search.'%')))
                ->orderBy('code')
                ->paginate(15),
        ];
    }

    public function delete(DeleteCostCodeAction $action, string $uuid): void
    {
        $costCode = CostCode::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($costCode, 404);

        $this->authorize('delete', $costCode);

        $action->execute($costCode);

        session()->flash('status', 'Cost code deleted.');
    }
}; ?>

<div>
    <x-page-header title="Cost Codes" description="A list of cost codes used to categorize BOQ and budget items.">
        <x-slot name="actions">
            <x-button :href="route('cost_codes.create')">Add cost code</x-button>
        </x-slot>
    </x-page-header>

    <div class="mt-6 flex items-center gap-4">
        <div class="w-full sm:max-w-xs">
            <x-search-input placeholder="Search cost codes…" />
        </div>
    </div>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Code</x-table.th>
                                <x-table.th>Name</x-table.th>
                                <x-table.th>Type</x-table.th>
                                <x-table.th>Parent</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($costCodes as $costCode)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $costCode->code }}</x-table.td>
                                    <x-table.td>{{ $costCode->name }}</x-table.td>
                                    <x-table.td>
                                        <x-badge tone="sky">{{ ucfirst($costCode->type->value) }}</x-badge>
                                    </x-table.td>
                                    <x-table.td>{{ $costCode->parent?->name ?? '—' }}</x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <a href="{{ route('cost_codes.edit', $costCode) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                                        <x-confirm-delete :uuid="$costCode->uuid" class="ml-4">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-empty-state title="No cost codes found." description="Add a cost code to get started.">
                                            <x-slot name="action">
                                                <x-button :href="route('cost_codes.create')">Add cost code</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $costCodes->links() }}
    </div>
</div>
