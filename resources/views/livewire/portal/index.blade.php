<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\ProjectPhoto;
use App\Domains\Sales\Models\ChangeOrder;
use App\Domains\Sales\Models\Invoice;

new #[Layout('layouts.app')] class extends Component {
    public function with(): array
    {
        $companyId = auth()->user()->company_id;

        return [
            'projects' => Project::with(['client', 'manager'])
                ->withCount('photos')
                ->where('company_id', $companyId)
                ->latest()
                ->get(),
            'pendingChangeOrderCount' => ChangeOrder::where('company_id', $companyId)
                ->where('status', 'pending')
                ->count(),
            'openInvoiceCount' => Invoice::where('company_id', $companyId)
                ->whereIn('status', ['draft', 'sent', 'partial', 'overdue'])
                ->count(),
        ];
    }
}; ?>

<div>
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">Client Portal</h1>
            <p class="mt-2 text-sm text-gray-700">A read-only overview of project progress for external stakeholders.</p>
        </div>
    </div>

    <dl class="grid grid-cols-1 gap-5 mt-8 sm:grid-cols-3">
        <div class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow ring-1 ring-black ring-opacity-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Active Projects</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $projects->where('status', \App\Domains\ProjectManagement\Enums\ProjectStatus::Active)->count() }}</dd>
        </div>
        <div class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow ring-1 ring-black ring-opacity-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Pending Change Orders</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $pendingChangeOrderCount }}</dd>
        </div>
        <div class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow ring-1 ring-black ring-opacity-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Open Invoices</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $openInvoiceCount }}</dd>
        </div>
    </dl>

    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Project</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Client</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Progress</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Photos</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Timeline</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($projects as $project)
                                <tr>
                                    <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-6">{{ $project->title }}</td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $project->client?->name ?? 'N/A' }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-700 bg-indigo-100 rounded-full">{{ $project->status->value }}</span>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ $project->start_date?->format('M j, Y') ?? '—' }} → {{ $project->end_date?->format('M j, Y') ?? '—' }}
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $project->photos_count }}</td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        @if ($project->status === \App\Domains\ProjectManagement\Enums\ProjectStatus::Completed)
                                            Completed
                                        @else
                                            In progress
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-8 text-sm text-center text-gray-500">
                                        No projects to display yet.
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
