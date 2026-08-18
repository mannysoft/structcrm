<?php

use Livewire\Volt\Component;
use App\Domains\Sales\Models\Lead;

new class extends Component {
    public function with(): array
    {
        return [
            'leads' => Lead::latest()->get(),
        ];
    }
}; ?>

<div>
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">Leads</h1>
            <p class="mt-2 text-sm text-gray-700">A list of all prospective clients and leads.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="/leads/create" wire:navigate class="block px-3 py-2 text-sm font-semibold text-center text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Add lead
            </a>
        </div>
    </div>
    
    <div class="flow-root mt-8">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Email</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Phone</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">View</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($leads as $lead)
                                <tr>
                                    <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-6">{{ $lead->first_name }} {{ $lead->last_name }}</td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $lead->email ?? 'N/A' }}</td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $lead->phone ?? 'N/A' }}</td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-700 rounded-md bg-blue-50 ring-1 ring-inset ring-blue-600/20">
                                            {{ ucfirst($lead->status->value) }}
                                        </span>
                                    </td>
                                    <td class="relative py-4 pl-3 pr-4 text-sm font-medium text-right whitespace-nowrap sm:pr-6">
                                        <a href="#" class="text-indigo-600 hover:text-indigo-900">View<span class="sr-only">, {{ $lead->first_name }}</span></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-8 text-sm text-center text-gray-500">
                                        No leads found. Add a lead to get started.
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
