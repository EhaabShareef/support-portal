{{-- resources/views/organizations/show.blade.php --}}
@extends('layouts.app')

@section('title', $organization->name)

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
    {{-- Left Column: Organization Details --}}
    <div class="space-y-4 md:row-span-3 h-full">
        {{-- Organization Details Card --}}
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 shadow-md">
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100 mb-4">Organization Details</h2>
            <dl class="grid grid-cols-1 gap-2">
                @php
                    $fields = [
                        'Name' => $organization->name,
                        'Company' => $organization->company,
                        'Contact' => $organization->company_contact,
                        'TIN No.' => $organization->tin_no,
                        'Email' => $organization->email,
                        'Phone' => $organization->phone,
                        '' => $organization->active_yn
                            ? '<span class="text-green-600 bg-green-200 py-1 px-3 rounded-md text-xs">Active</span>'
                            : '<span class="text-red-600 bg-red-200 py-1 px-3 rounded-md text-xs">Inactive</span>',
                    ];
                @endphp

                @foreach ($fields as $label => $value)
                    <div>
                        <dt class="font-semibold text-neutral-500 uppercase text-xs mt-2 md:mt-6">{{ $label }}</dt>
                        <dd class="mt-1 text-sm">{!! $value !!}</dd>
                    </div>
                @endforeach
            </dl>
            <div class="space-x-2 flex mt-6 border-t border-white/20 pt-4">
                <a href="{{ route('organizations.edit', $organization) }}"
                    class="p-2 border-blue-400 text-blue-400 hover:bg-blue-500 hover:text-white hover:border-neutral-100 rounded-md text-xs transition-all">
                    <x-heroicon-o-cog-6-tooth class="inline size-4" /> Edit
                </a>
                <a href="{{ route('organizations.confirm-delete', $organization) }}"
                    class="p-2 border-red-400 text-red-400 hover:bg-red-500 hover:text-white hover:border-neutral-100 rounded-md text-xs transition-all">
                    <x-heroicon-o-trash class="inline size-4" /> Delete
                </a>
            </div>
        </div>
    </div>

    {{-- Right Column: Content in 3 Rows --}}
    <div class="md:col-span-2 space-y-6">
        {{-- Row 1: Users --}}
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 shadow-md">
            <h2 class="flex items-center text-lg font-semibold mb-4 text-neutral-800 dark:text-neutral-100">
                <x-heroicon-o-users class="inline size-5 mr-2" />Users</h2>
            <ul class="space-y-2">
                @forelse($organization->users as $user)
                    <li class="flex justify-between items-center bg-white/20 dark:bg-gray-700/50 rounded-md p-4 shadow-sm hover:bg-white/30 transition-all">
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-user-circle class="size-10" />
                            <div>
                                <div class="font-medium text-neutral-800 dark:text-neutral-100">{{ $user->name }}</div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $user->email }}</div>
                            </div>
                        </div>
                        <a href="#" class="px-3 py-2 text-xs transition-all">
                            <x-heroicon-o-adjustments-vertical class="inline h-4 w-4" />
                        </a>
                    </li>
                @empty
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">No users assigned.</p>
                @endforelse
            </ul>
        </div>

        {{-- Row 2: Contracts --}}
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 shadow-md">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100">
                    <x-heroicon-o-clipboard-document-check class="inline size-5 mr-2" />Contracts</h2>
                <a href="{{ route('contracts.create', ['org_id' => $organization->id]) }}"
                   class="px-3 py-2 bg-neutral-900 hover:bg-neutral-800 text-white rounded-md text-xs">
                    <x-heroicon-o-plus-circle class="inline h-4 w-4 mr-1" />New Contract
                </a>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @forelse($organization->contracts as $contract)
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 shadow hover:bg-white/20 transition space-y-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-base font-semibold text-neutral-800 dark:text-neutral-100 mb-2">
                                    {{ $contract->department->name ?? '-' }}
                                    <span class="ml-1 text-xs font-medium px-2 py-0.5 rounded bg-neutral-200 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300">
                                        {{ ucfirst($contract->status) }}
                                    </span>
                                </div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400">
                                    Start: {{ $contract->start_date->format('d-m-Y') }}<br>
                                    Expire: {{ $contract->end_date ? $contract->end_date->format('d-m-Y') : 'Ongoing' }}
                                </div>
                            </div>
                            <a href="{{ route('contracts.edit', $contract) }}">
                                <x-heroicon-o-arrow-right-circle class="inline size-6 stroke-1 hover:stroke-2 transition-all" />
                            </a>
                        </div>
                        @if ($contract->csi_remarks)
                            <div class="text-xs font-semibold text-neutral-700 dark:text-neutral-300 mt-1">
                                CSI# {{ $contract->csi_remarks }}
                            </div>
                        @endif
                        @if ($contract->is_hardware)
                            <div class="text-xs text-green-600 flex items-center mt-1">
                                <x-heroicon-o-cube class="size-3 mr-1" />Hardware Included
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">No contracts yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Row 3: Hardware --}}
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 shadow-md">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100">
                    <x-heroicon-o-cube class="inline size-5 mr-2" />Hardware</h2>
                <a href="{{ route('hardware.create', ['org_id' => $organization->id]) }}"
                   class="px-3 py-2 bg-neutral-900 text-white rounded-md text-xs transition-all">
                    <x-heroicon-o-plus-circle class="inline h-4 w-4 mr-1" />New Hardware
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($organization->hardware as $hw)
                    <div class="bg-white/20 rounded-md p-4 shadow-sm">
                        <div class="font-medium text-neutral-800 dark:text-neutral-100">
                            {{ $hw->hardware_type }} / {{ $hw->hardware_model }}
                        </div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400">SN: {{ $hw->serial_number }}</div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400">Warranty Exp: {{ $hw->warranty_expiration->format('d-m-Y') }}</div>
                        <div class="mt-2 flex space-x-2">
                            <a href="{{ route('hardware.edit', $hw) }}" class="text-blue-600 hover:underline text-xs">
                                <x-heroicon-o-pencil class="inline h-4 w-4 mr-1" />Edit
                            </a>
                            <a href="{{ route('hardware.confirm-delete', $hw) }}" class="text-red-600 hover:underline text-xs">
                                <x-heroicon-o-trash class="inline h-4 w-4 mr-1" />Delete
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">No hardware records yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
