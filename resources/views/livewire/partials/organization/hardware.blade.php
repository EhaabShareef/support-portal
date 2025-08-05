<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    @forelse($organization->hardware as $hw)
        <div
            class="bg-white/10 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 shadow-sm dark:shadow-neutral-200/10 transition transform hover:-translate-y-1 space-y-1">

            <div class="flex justify-between items-start">
                <div>
                    <div class="text-base font-semibold text-neutral-800 dark:text-neutral-100 mb-1">
                        {{ $hw->hardware_type }} / {{ $hw->hardware_model }}
                        @if ($hw->is_active)
                            <span
                                class="ml-2 text-xs font-medium px-2 py-0.5 rounded bg-green-200 text-green-900">
                                Active
                            </span>
                        @endif
                    </div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">
                        SN: {{ $hw->serial_number }} • Exp: {{ $hw->warranty_expiration->format('d-m-Y') }}
                    </div>
                </div>
            </div>
        </div>
    @empty
        <p class="text-sm text-neutral-500 dark:text-neutral-400">No hardware records yet.</p>
    @endforelse
</div>
