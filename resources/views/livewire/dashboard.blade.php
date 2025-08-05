<div class="space-y-6" wire:poll.30s="refreshData">
    {{-- Page Header --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                    <x-heroicon-o-chart-bar-square class="h-8 w-8 text-sky-500" />
                    Dashboard
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                    Welcome back, {{ auth()->user()->name }} | {{ $userRole }}
                </p>
            </div>
            <button wire:click="refreshData" 
                class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
                <x-heroicon-o-arrow-path class="h-4 w-4 mr-2" />
                Refresh
            </button>
        </div>
    </div>

    @if($userRole === 'Super Admin' || $userRole === 'Admin')
        @include('livewire.dashboard.admin-dashboard')
    @elseif($userRole === 'Agent')
        @include('livewire.dashboard.agent-dashboard')
    @else
        @include('livewire.dashboard.client-dashboard')
    @endif

    {{-- Loading States --}}
    <div wire:loading wire:target="refreshData" class="fixed inset-0 bg-black/20 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 shadow-xl">
            <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-sky-600"></div>
                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Updating dashboard...</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('dataRefreshed', () => {
            console.log('Dashboard data refreshed');
        });
    });
</script>
@endpush
