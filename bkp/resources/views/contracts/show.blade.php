@extends('layouts.app')

@section('title', 'Contract Details')

@section('content')
    <div class="space-y-6">
        {{-- Page Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <x-heroicon-o-clipboard-document-check class="h-8 w-8 text-neutral-600 dark:text-neutral-300" />
                <h1 class="text-3xl font-bold">Contract Details</h1>
            </div>
            <div class="space-x-2">
                <a href="{{ route('contracts.edit', $contract) }}"
                   class="inline-flex items-center px-3 py-1.5 text-sm text-blue-600 border border-blue-300 rounded-md hover:bg-blue-50 dark:hover:bg-blue-200">
                    <x-heroicon-o-cog-6-tooth class="h-4 w-4 mr-1" /> Edit
                </a>
                <a href="{{ route('contracts.confirm-delete', $contract) }}"
                   class="inline-flex items-center px-3 py-1.5 text-sm text-red-600 border border-red-300 rounded-md hover:bg-red-50 dark:hover:bg-red-200">
                    <x-heroicon-o-trash class="h-4 w-4 mr-1" /> Delete
                </a>
            </div>
        </div>

        {{-- Contract Card --}}
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 shadow-lg space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="text-xs uppercase text-neutral-500">Organization</div>
                    <div class="text-base font-semibold text-neutral-800 dark:text-neutral-100">
                        {{ $contract->organization->name }}
                    </div>
                </div>

                <div>
                    <div class="text-xs uppercase text-neutral-500">Department</div>
                    <div class="text-base font-semibold text-neutral-800 dark:text-neutral-100">
                        {{ $contract->department->name }}
                    </div>
                </div>

                <div>
                    <div class="text-xs uppercase text-neutral-500">Contract Period</div>
                    <div class="text-sm text-neutral-700 dark:text-neutral-200">
                        {{ $contract->start_date->format('d M Y') }} – {{ $contract->end_date ? $contract->end_date->format('d M Y') : 'Ongoing' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs uppercase text-neutral-500">Status</div>
                    <div class="inline-block px-2 py-1 text-xs rounded-md bg-neutral-200 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300">
                        {{ ucfirst($contract->status) }}
                    </div>
                </div>

                @if ($contract->csi_remarks)
                    <div class="md:col-span-2">
                        <div class="text-xs uppercase text-neutral-500">CSI / Remarks</div>
                        <div class="text-sm text-neutral-700 dark:text-neutral-300">{{ $contract->csi_remarks }}</div>
                    </div>
                @endif

                @if ($contract->is_hardware)
                    <div class="md:col-span-2">
                        <div class="text-xs uppercase text-green-600 flex items-center">
                            <x-heroicon-o-cube class="h-4 w-4 mr-1" /> Hardware Included
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Hardware Table --}}
        @if ($contract->hardware && $contract->hardware->count())
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 shadow-lg">
                <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100 mb-4">Linked Hardware</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left text-neutral-700 dark:text-neutral-200">
                        <thead class="border-b text-xs uppercase text-neutral-500">
                            <tr>
                                <th class="px-4 py-2">Type</th>
                                <th class="px-4 py-2">Model</th>
                                <th class="px-4 py-2">Serial #</th>
                                <th class="px-4 py-2">Purchase Date</th>
                                <th class="px-4 py-2">Warranty Expiry</th>
                                <th class="px-4 py-2">Remarks</th>
                                <th class="px-4 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contract->hardware as $hw)
                                <tr class="border-b border-white/10">
                                    <td class="px-4 py-2">{{ $hw->hardware_type }}</td>
                                    <td class="px-4 py-2">{{ $hw->hardware_model }}</td>
                                    <td class="px-4 py-2">{{ $hw->serial_number }}</td>
                                    <td class="px-4 py-2">{{ $hw->purchase_date?->format('d M Y') }}</td>
                                    <td class="px-4 py-2">{{ $hw->warranty_expiration?->format('d M Y') }}</td>
                                    <td class="px-4 py-2">{{ $hw->remarks }}</td>
                                    <td class="px-4 py-2">
                                        <span class="text-xs px-2 py-1 rounded {{ $hw->is_active ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                                            {{ $hw->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
