<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Domains\ProjectManagement\Actions\DeleteDocumentAction;
use App\Domains\ProjectManagement\Models\Document;

new #[Layout('layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'documents' => Document::with('project')->latest()->get(),
        ];
    }

    public function delete(DeleteDocumentAction $action, string $uuid): void
    {
        $document = Document::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($document, 404);

        $this->authorize('delete', $document);

        $action->execute($document);

        session()->flash('status', 'Document deleted.');
    }
}; ?>

<div>
    <x-page-header title="Documents" description="Blueprints, permits, contracts and specifications.">
        <x-slot name="actions">
            <x-button :href="route('documents.create')">Upload document</x-button>
        </x-slot>
    </x-page-header>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Name</x-table.th>
                                <x-table.th>Category</x-table.th>
                                <x-table.th>Project</x-table.th>
                                <x-table.th>Uploaded</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($documents as $document)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $document->name }}</x-table.td>
                                    <x-table.td>{{ $document->category->value }}</x-table.td>
                                    <x-table.td>{{ $document->project?->title ?? 'N/A' }}</x-table.td>
                                    <x-table.td>{{ $document->created_at?->format('M j, Y') }}</x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <x-confirm-delete :uuid="$document->uuid">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-empty-state title="No documents yet." description="Upload your first document.">
                                            <x-slot name="action">
                                                <x-button :href="route('documents.create')">Upload document</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>
</div>
