{{-- resources/views/hardware/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Hardware Inventory')

@section('content')
    {{-- Page Header --}}
    <x-ui.page-header title="Hardware Inventory" subtitle="Manage all hardware linked to contracts" icon="heroicon-o-cube"
        :search-action="route('hardware.index')" :search-query="request('search')" :create-route="route('hardware.create')" create-label="+ New" />

    {{-- Alert --}}
    @if (session('alert'))
        <x-alert :type="session('alert')['type']" :title="session('alert')['title']" :message="session('alert')['message']" />
    @endif

    {{-- Hardware Table --}}
    <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 shadow-lg overflow-x-auto">
        <table class="min-w-full text-sm text-left text-neutral-700 dark:text-neutral-200">
                    <thead class="border-b text-xs uppercase text-neutral-500">
                        <tr>
                            <th class="px-4 py-2">Contract</th>
                            <th class="px-4 py-2">Type</th>
                            <th class="px-4 py-2">Model</th>
                            <th class="px-4 py-2">Serial #</th>
                            <th class="px-4 py-2">Purchase</th>
                            <th class="px-4 py-2">Warranty</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($hardware as $item)
                            <tr class="border-b border-white/10">
                                <td class="px-4 py-2 font-semibold">
                                    {{ $item->contract->organization->name ?? '-' }}
                                </td>
                                <td class="px-4 py-2">{{ $item->hardware_type }}</td>
                                <td class="px-4 py-2">{{ $item->hardware_model }}</td>
                                <td class="px-4 py-2">{{ $item->serial_number }}</td>
                                <td class="px-4 py-2">{{ $item->purchase_date?->format('d M Y') }}</td>
                                <td class="px-4 py-2">{{ $item->warranty_expiration?->format('d M Y') }}</td>
                                <td class="px-4 py-2">
                                    <span class="text-xs px-2 py-1 rounded {{ $item->is_active ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="py-2 text-center">
                                    <div class="flex justify-center">
                                        <a href="{{ route('hardware.edit', $item) }}" class="text-blue-500 hover:bg-blue-100 py-2 px-3 rounded text-xs transition-all">
                                            <x-heroicon-o-cog-6-tooth class="size-5" />
                                        </a>
                                        <a href="{{ route('hardware.confirm-delete', $item) }}" class="text-red-500 hover:bg-red-100 py-2 px-3 rounded text-xs transition-all">
                                            <x-heroicon-o-trash class="size-5" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-6 text-neutral-500 dark:text-neutral-400">
                                    No hardware records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $hardware->withQueryString()->links() }}
        </div>
    </div>
@endsection
