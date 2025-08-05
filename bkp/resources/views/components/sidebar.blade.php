{{-- resources/views/layouts/sidebar.blade.php --}}
<aside class="hidden md:block w-64 bg-neutral-100 dark:bg-neutral-800 border-r border-neutral-200 dark:border-neutral-300">
  <nav class="p-4 space-y-2 text-sm">
    <a href="{{ route('dashboard') }}"
       class="flex items-center px-3 py-2 rounded-md hover:bg-neutral-700 dark:hover:bg-neutral-300">
      <x-heroicon-o-home class="h-5 w-5 mr-2 text-neutral-600 dark:text-neutral-300"/> 
      <span>Dashboard</span>
    </a>

    @can('tickets.view')
    <a href="{{ route('tickets.index') }}"
       class="flex items-center px-3 py-2 rounded-md hover:bg-neutral-700 dark:hover:bg-neutral-300">
      <x-heroicon-o-ticket class="h-5 w-5 mr-2 text-neutral-600 dark:text-neutral-300"/>
      <span>Tickets</span>
    </a>
    @endcan

    @role('Admin')
    <a href="{{ route('admin.users.index') }}"
       class="flex items-center px-3 py-2 rounded-md hover:bg-neutral-700 dark:hover:bg-neutral-300">
      <x-heroicon-o-users class="h-5 w-5 mr-2 text-neutral-600 dark:text-neutral-300"/>
      <span>Users</span>
    </a>
    @endrole

    <a href="{{ route('tickets.index') }}"
       class="flex items-center px-3 py-2 rounded-md hover:bg-neutral-700 dark:hover:bg-neutral-300">
      <x-heroicon-o-ticket class="h-5 w-5 mr-2 text-neutral-600 dark:text-neutral-300"/>
      <span>Tickets</span>
    </a>

    <a href="{{ route('organizations.index') }}"
       class="flex items-center px-3 py-2 rounded-md hover:bg-neutral-700 dark:hover:bg-neutral-300">
      <x-heroicon-o-building-office-2 class="h-5 w-5 mr-2 text-neutral-600 dark:text-neutral-300"/>
      <span>Organizations</span>
    </a>

    <a href="{{ route('contracts.index') }}"
       class="flex items-center px-3 py-2 rounded-md hover:bg-neutral-700 dark:hover:bg-neutral-300">
      <x-heroicon-o-document-text class="h-5 w-5 mr-2 text-neutral-600 dark:text-neutral-300"/>
      <span>Contracts</span>
    </a>

    <a href="{{ route('hardware.index') }}"
       class="flex items-center px-3 py-2 rounded-md hover:bg-neutral-700 dark:hover:bg-neutral-300">
      <x-heroicon-o-cube class="h-5 w-5 mr-2 text-neutral-600 dark:text-neutral-300"/>
      <span>Hardware</span>
    </a>

    {{-- TODO: Add mobile toggle here --}}
  </nav>
</aside>
