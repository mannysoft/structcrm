<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Domains\Finance\Actions\DeleteProgressReportAction;
use App\Domains\Finance\Models\ProgressReport;

new #[Layout('layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'progressReports' => ProgressReport::with(['project', 'items'])->latest()->get(),
        ];
    }

    public function delete(DeleteProgressReportAction $action, string $uuid): void
    {
        $progressReport = ProgressReport::where('uuid', $uuid)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        abort_unless($progressReport, 404);

        $this->authorize('delete', $progressReport);

        $action->execute($progressReport);

        session()->flash('status', 'Progress report deleted.');
    }
}; ?>

<div>
    <x-page-header title="Progress Reports" description="Site accomplishment reports tied to your BOQ.">
        <x-slot name="actions">
            <x-button :href="route('progress_reports.create')">Add report</x-button>
        </x-slot>
    </x-page-header>

    <x-table>
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.th>Number</x-table.th>
                                <x-table.th>Project</x-table.th>
                                <x-table.th>Date</x-table.th>
                                <x-table.th>Status</x-table.th>
                                <x-table.th>Accomplished</x-table.th>
                                <x-table.th>
                                    <span class="sr-only">Actions</span>
                                </x-table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($progressReports as $report)
                                <tr>
                                    <x-table.td class="font-medium text-gray-900">{{ $report->report_number }}</x-table.td>
                                    <x-table.td>{{ $report->project?->title ?? 'N/A' }}</x-table.td>
                                    <x-table.td>{{ $report->report_date?->format('M j, Y') }}</x-table.td>
                                    <x-table.td>
                                        <x-badge tone="sky">{{ ucfirst($report->status->value) }}</x-badge>
                                    </x-table.td>
                                    <x-table.td>{{ number_format($report->totalAccomplished() / 100, 2) }}</x-table.td>
                                    <x-table.td class="text-right font-medium">
                                        <a href="{{ route('progress_reports.edit', $report) }}" wire:navigate class="text-brand-600 hover:text-brand-700">Edit</a>
                                        <x-confirm-delete :uuid="$report->uuid" class="ml-4">Delete</x-confirm-delete>
                                    </x-table.td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <x-empty-state title="No progress reports found." description="Add a report to get started.">
                                            <x-slot name="action">
                                                <x-button :href="route('progress_reports.create')">Add report</x-button>
                                            </x-slot>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
    </x-table>
</div>
