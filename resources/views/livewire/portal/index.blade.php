<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\DecideChangeOrderAction;
use App\Domains\Sales\Actions\MarkInvoicePaidAction;
use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Enums\InvoiceStatus;
use App\Domains\Sales\Models\ChangeOrder;
use App\Domains\Sales\Models\Invoice;

new #[Layout('layouts.app')] class extends Component {
    public function with(): array
    {
        $clientId = $this->clientId();

        return [
            'clientName' => auth('client')->user()->client?->name,
            'projects' => Project::with('client')
                ->withCount('photos')
                ->where('client_id', $clientId)
                ->latest()
                ->get(),
            'pendingChangeOrders' => ChangeOrder::with('project')
                ->where('status', ChangeOrderStatus::Pending)
                ->whereHas('project', fn ($query) => $query->where('client_id', $clientId))
                ->latest()
                ->get(),
            'openInvoices' => Invoice::with('project')
                ->whereIn('status', [
                    InvoiceStatus::Draft,
                    InvoiceStatus::Sent,
                    InvoiceStatus::Partial,
                    InvoiceStatus::Overdue,
                ])
                ->whereHas('project', fn ($query) => $query->where('client_id', $clientId))
                ->latest()
                ->get(),
        ];
    }

    public function approveChangeOrder(DecideChangeOrderAction $action, string $uuid): void
    {
        $action->execute($this->changeOrderFor($uuid), ChangeOrderStatus::Approved);

        session()->flash('status', 'Change order approved.');
    }

    public function rejectChangeOrder(DecideChangeOrderAction $action, string $uuid): void
    {
        $action->execute($this->changeOrderFor($uuid), ChangeOrderStatus::Rejected);

        session()->flash('status', 'Change order rejected.');
    }

    public function markInvoicePaid(MarkInvoicePaidAction $action, string $uuid): void
    {
        $invoice = Invoice::where('uuid', $uuid)
            ->whereHas('project', fn ($query) => $query->where('client_id', $this->clientId()))
            ->first();

        abort_unless($invoice, 404);

        $action->execute($invoice);

        session()->flash('status', 'Invoice marked as paid.');
    }

    private function clientId(): int
    {
        return auth('client')->user()->client_id;
    }

    private function changeOrderFor(string $uuid): ChangeOrder
    {
        $changeOrder = ChangeOrder::where('uuid', $uuid)
            ->whereHas('project', fn ($query) => $query->where('client_id', $this->clientId()))
            ->first();

        abort_unless($changeOrder, 404);

        return $changeOrder;
    }
}; ?>

<div>
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">Client Portal</h1>
            <p class="mt-2 text-sm text-gray-700">
                @if ($clientName)
                    Welcome back, {{ $clientName }}. Here's the latest on your projects.
                @else
                    Here's the latest on your projects.
                @endif
            </p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <form method="POST" action="{{ route('portal.logout') }}">
                @csrf
                <button type="submit" class="block px-3 py-2 text-sm font-semibold text-center text-gray-700 bg-white rounded-md shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Log out
                </button>
            </form>
        </div>
    </div>

    <dl class="grid grid-cols-1 gap-5 mt-8 sm:grid-cols-3">
        <div class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow ring-1 ring-black ring-opacity-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Active Projects</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $projects->where('status', ProjectStatus::Active)->count() }}</dd>
        </div>
        <div class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow ring-1 ring-black ring-opacity-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Pending Change Orders</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $pendingChangeOrders->count() }}</dd>
        </div>
        <div class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow ring-1 ring-black ring-opacity-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Open Invoices</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $openInvoices->count() }}</dd>
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
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Timeline</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Photos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($projects as $project)
                                <tr>
                                    <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-6">{{ $project->title }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-brand-700 bg-brand-100 rounded-full">{{ $project->status->value }}</span>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ $project->start_date?->format('M j, Y') ?? '—' }} → {{ $project->end_date?->format('M j, Y') ?? '—' }}
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $project->photos_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-8 text-sm text-center text-gray-500">
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

    <div class="grid grid-cols-1 gap-6 mt-8 lg:grid-cols-2">
        <section class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                <h2 class="text-base font-semibold leading-6 text-gray-900">Pending Change Orders</h2>
                <p class="mt-1 text-sm text-gray-500">Review and approve or reject scope changes.</p>
            </div>
            <ul class="divide-y divide-gray-200">
                @forelse ($pendingChangeOrders as $changeOrder)
                    <li class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $changeOrder->project?->title ?? 'N/A' }}</p>
                                <p class="mt-1 text-sm text-gray-500 truncate">{{ $changeOrder->co_number }} — {{ Str::limit($changeOrder->description, 80) }}</p>
                                <p class="mt-1 text-sm font-medium text-gray-900">${{ number_format($changeOrder->amount / 100, 2) }}</p>
                            </div>
                            <div class="flex items-center ml-4 gap-2">
                                <button wire:click="approveChangeOrder('{{ $changeOrder->uuid }}')" class="px-3 py-1.5 text-sm font-semibold text-white bg-green-600 rounded-md shadow-sm hover:bg-green-500">Approve</button>
                                <button wire:click="rejectChangeOrder('{{ $changeOrder->uuid }}')" class="px-3 py-1.5 text-sm font-semibold text-white bg-red-600 rounded-md shadow-sm hover:bg-red-500">Reject</button>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-8 text-sm text-center text-gray-500 sm:px-6">
                        No pending change orders.
                    </li>
                @endforelse
            </ul>
        </section>

        <section class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                <h2 class="text-base font-semibold leading-6 text-gray-900">Open Invoices</h2>
                <p class="mt-1 text-sm text-gray-500">Review outstanding balances for your projects.</p>
            </div>
            <ul class="divide-y divide-gray-200">
                @forelse ($openInvoices as $invoice)
                    <li class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $invoice->project?->title ?? 'N/A' }}</p>
                                <p class="mt-1 text-sm text-gray-500">{{ $invoice->invoice_number }} — {{ ucfirst($invoice->status->value) }}</p>
                                <p class="mt-1 text-sm font-medium text-gray-900">${{ number_format($invoice->total / 100, 2) }} <span class="font-normal text-gray-500">due {{ $invoice->due_date?->format('M j, Y') ?? 'N/A' }}</span></p>
                            </div>
                            <div class="ml-4">
                                <button wire:click="markInvoicePaid('{{ $invoice->uuid }}')" wire:confirm="Mark this invoice as paid?" class="px-3 py-1.5 text-sm font-semibold text-white bg-brand-600 rounded-md shadow-sm hover:bg-brand-500">Mark as paid</button>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-8 text-sm text-center text-gray-500 sm:px-6">
                        No open invoices.
                    </li>
                @endforelse
            </ul>
        </section>
    </div>
</div>
