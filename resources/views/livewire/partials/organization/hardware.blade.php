<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    @forelse($organization->hardware as $hw)
        <div class="bg-white/10 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 shadow-sm dark:shadow-neutral-200/10 transition transform hover:-translate-y-1 space-y-1">
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-base font-semibold text-neutral-800 dark:text-neutral-100 mb-1">
                        {{ $hw->type?->name }} / {{ $hw->model }}
                    </div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">
                        Brand: {{ $hw->brand }} • Qty: {{ $hw->quantity }}
                    </div>
                    @if($hw->serial_required)
                        <div class="text-xs text-neutral-500 dark:text-neutral-400">
                            Serials: {{ $hw->serials->count() }} / {{ $hw->quantity }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <p class="text-sm text-neutral-500 dark:text-neutral-400">No hardware records yet.</p>
    @endforelse
</div>
