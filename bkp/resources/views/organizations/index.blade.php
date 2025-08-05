{{-- resources/views/organizations/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Organizations')

@section('content')
    {{-- Top bar --}}
    <x-ui.page-header 
        title="Organizations" subtitle="Manage Organizations and their contracts"
        icon="heroicon-o-building-office-2" 
        :search-action="route('organizations.index')" :search-query="request('search')" 
        :create-route="route('organizations.create')" />

    {{-- Alert Notification --}}
    @if (session('alert'))
        <x-alert :type="session('alert')['type']" :title="session('alert')['title']" :message="session('alert')['message']" />
    @endif

    {{-- List container --}}
    <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 space-y-4 shadow-lg">
        @forelse($organizations as $org)
            <div
                class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 flex flex-col lg:flex-row lg:items-center justify-between space-y-4 lg:space-y-0 shadow-md hover:bg-white/20 transition">

                {{-- Icon + name/email --}}
                <div class="flex items-start space-x-4 w-full lg:w-1/4">
                    <div>
                        <div class="text-lg font-medium text-neutral-800 dark:text-neutral-100">
                            {{ $org->name }}
                        </div>
                        <div class="text-xs text-neutral-600 dark:text-neutral-400 flex flex-wrap items-center gap-x-2 mt-1">
                            <x-heroicon-o-envelope class="size-3" /> {{ $org->email }}
                            <x-heroicon-o-phone class="size-3" /> {{ $org->phone }}
                        </div>
                    </div>
                </div>

                {{-- Company --}}
                <div class="text-left w-full lg:w-1/4">
                    <div class="uppercase font-semibold text-xs text-neutral-400 dark:text-neutral-400">Parent Company</div>
                    <div class="text-sm font-medium text-neutral-700 dark:text-neutral-100">{{ $org->company }}</div>
                </div>

                {{-- Tin --}}
                <div class="text-left w-full lg:w-1/5">
                    <div class="uppercase text-xs text-neutral-400 dark:text-neutral-400">Tin No.</div>
                    <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100">{{ $org->tin_no }}</div>
                </div>

                {{-- Contracts --}}
                <div class="text-left w-full lg:w-1/6">
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Contracts</div>
                    <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100">
                        {{ $org->contracts()->count() }}
                    </div>
                </div>

                {{-- More --}}
                <div class="text-left w-full lg:w-auto">
                    <a href="{{ route('organizations.show', $org) }}"
                        class="flex items-center text-neutral-600 dark:text-neutral-300 hover:text-neutral-800 dark:hover:text-neutral-100 text-sm">
                        <span class="mr-1">More</span>
                        <x-heroicon-o-chevron-right class="h-5 w-5" />
                    </a>
                </div>
            </div>
        @empty
            <p class="text-center text-neutral-500 dark:text-neutral-400 py-8">
                No organizations found.
            </p>
        @endforelse

        {{-- Pagination --}}
        <x-ui.pagination :paginator="$organizations" />
        
    </div>
@endsection
