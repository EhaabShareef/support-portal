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