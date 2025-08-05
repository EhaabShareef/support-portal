<aside
    x-bind:class="sidebarCollapsed ? 'w-20' : 'w-64'"
    class="hidden lg:flex flex-col fixed lg:static inset-y-0 left-0 z-40
           bg-white/60 dark:bg-neutral-800/50 backdrop-blur
           border-r border-neutral-200 dark:border-neutral-700
           shadow-lg transition-all duration-300 ease-in-out"
>
    <nav class="p-4 space-y-1 text-sm text-neutral-700 dark:text-neutral-100">

        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-neutral-200/50 dark:hover:bg-neutral-700/50 transition">
            <x-heroicon-o-home class="h-5 w-5 text-neutral-600 dark:text-neutral-300"/>
            <span x-show="!sidebarCollapsed" class="transition-all duration-200 origin-left">Dashboard</span>
        </a>

        @role('Admin')
        <a href="{{ route('admin.users.index') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-neutral-200/50 dark:hover:bg-neutral-700/50 transition">
            <x-heroicon-o-users class="h-5 w-5 text-neutral-600 dark:text-neutral-300"/>
            <span x-show="!sidebarCollapsed" class="transition-all duration-200 origin-left">Users</span>
        </a>
        @endrole

        <a href="{{ route('organizations.index') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-neutral-200/50 dark:hover:bg-neutral-700/50 transition">
            <x-heroicon-o-building-office-2 class="h-5 w-5 text-neutral-600 dark:text-neutral-300"/>
            <span x-show="!sidebarCollapsed" class="transition-all duration-200 origin-left">Organizations</span>
        </a>

        <a href="{{ route('tickets.index') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-neutral-200/50 dark:hover:bg-neutral-700/50 transition">
            <x-heroicon-o-ticket class="h-5 w-5 text-neutral-600 dark:text-neutral-300"/>
            <span x-show="!sidebarCollapsed" class="transition-all duration-200 origin-left">Tickets</span>
        </a>

    </nav>
</aside>
