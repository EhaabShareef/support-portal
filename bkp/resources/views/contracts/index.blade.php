@extends('layouts.app')

@section('title', 'Organization Contracts')

@section('content')
    {{-- Top bar --}}
    <x-ui.page-header 
        title="Organization Contracts"
        subtitle="View and manage organization contract records"
        icon="heroicon-o-clipboard-document-check"
        :search-action="route('contracts.index')"
        :search-query="request('search')"
        :create-route="route('contracts.create')"
        create-label="+ New"
    />

    {{-- Alert Notification --}}
    @if (session('alert'))
        <x-alert
            :type="session('alert')['type']"
            :title="session('alert')['title']"
            :message="session('alert')['message']"
        />
    @endif

    {{-- List container --}}
    <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 space-y-4 shadow-lg">
        @forelse ($contracts as $contract)
            <div
                class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 flex flex-col lg:flex-row lg:items-center justify-between space-y-4 lg:space-y-0 shadow-lg hover:bg-white/20 transition">

                {{-- Organization Name + Department --}}
                <div class="w-full lg:w-1/4">
                    <div class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                        {{ $contract->organization->name }}
                    </div>
                    <div class="text-xs text-neutral-600 dark:text-neutral-400 mt-1">
                        {{ $contract->department->name }}
                    </div>
                </div>

                {{-- Contract Dates --}}
                <div class="w-full lg:w-1/4">
                    <div class="text-xs uppercase text-neutral-500 dark:text-neutral-400">Contract Period</div>
                    <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100">
                        {{ $contract->start_date->format('d M Y') }} 
                        — 
                        {{ $contract->end_date ? $contract->end_date->format('d M Y') : 'Ongoing' }}
                    </div>
                </div>

                {{-- Hardware + Status --}}
                <div class="w-full lg:w-1/4">
                    <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100">
                        {{ $contract->is_hardware ? 'Hardware' : 'Software' }}
                    </div>
                    <div class="text-xs mt-1 text-neutral-500 dark:text-neutral-400">Status: 
                        <span class="font-medium">{{ ucfirst($contract->status) }}</span>
                    </div>
                </div>

                {{-- More --}}
                <div class="text-left w-full lg:w-auto">
                    <a href="{{ route('contracts.show', $contract) }}"
                        class="flex items-center text-neutral-600 dark:text-neutral-300 hover:text-neutral-800 dark:hover:text-neutral-100 text-sm">
                        <span class="mr-1">More</span>
                        <x-heroicon-o-chevron-right class="h-5 w-5" />
                    </a>
                </div>
            </div>
        @empty
            <p class="text-center text-neutral-500 dark:text-neutral-400 py-8">
                No organization contracts found.
            </p>
        @endforelse

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $contracts->withQueryString()->links() }}
        </div>
    </div>
@endsection
