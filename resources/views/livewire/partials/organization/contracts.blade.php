<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 ">
    @forelse($organization->contracts as $contract)
        <div
            class="bg-white/10 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 shadow-sm dark:shadow-neutral-200/10 transition transform hover:-translate-y-1 space-y-2">

            <div class="flex justify-between items-start">
                <div>
                    <div
                        class="flex flex-wrap items-center text-base font-semibold text-neutral-800 dark:text-neutral-100 mb-2 gap-2">
                        <span>{{ $contract->department->name ?? '-' }}</span>

                        @if ($contract->is_hardware)
                            <span class="text-xs font-medium px-2 py-0.5 rounded bg-orange-200 text-orange-900">
                                Hardware
                            </span>
                        @endif

                        <span
                            class="text-xs font-medium px-2 py-0.5 rounded 
                                     {{ $contract->status === 'active'
                                         ? 'bg-green-200 text-green-800'
                                         : 'bg-neutral-200 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300' }}">
                            {{ ucfirst($contract->status) }}
                        </span>
                    </div>

                    <div class="text-xs text-neutral-500 dark:text-neutral-400">
                        Start: {{ $contract->start_date->format('d-m-Y') }} /
                        Expire: {{ $contract->end_date ? $contract->end_date->format('d-m-Y') : 'Ongoing' }}
                    </div>
                </div>
            </div>
        </div>
    @empty
        <p class="text-sm text-neutral-500 dark:text-neutral-400">No contracts yet.</p>
    @endforelse
</div>
