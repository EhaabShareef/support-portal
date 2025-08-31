<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                    <x-heroicon-o-cog-6-tooth class="h-8 w-8" />
                    System Settings
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Configure your support portal preferences and system behavior</p>
            </div>
        </div>
    </div>

    {{-- Settings Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {{-- General Settings --}}
        <a href="{{ route('settings.general') }}" 
           class="group bg-white dark:bg-neutral-800 rounded-lg shadow-md hover:shadow-xl transition-all duration-500 hover:-translate-y-2 hover:scale-[1.02] border border-neutral-200 dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 overflow-hidden">
            <div class="p-6">
                <div class="flex items-start space-x-3">
                    <div class="relative">
                        <div class="h-10 w-10 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
                            <x-heroicon-o-cog-6-tooth class="h-5 w-5 text-neutral-600 dark:text-neutral-400" />
                        </div>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
                            General
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                            System preferences, themes, and basic configuration
                        </p>
                        
                        <div class="flex items-center text-blue-600 dark:text-blue-400 opacity-0 group-hover:opacity-100 transition-all duration-500 transform group-hover:translate-x-2">
                            <span class="text-sm font-medium">Configure</span>
                            <x-heroicon-o-arrow-right class="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300" />
                        </div>
                    </div>
                </div>
            </div>
        </a>

        {{-- Ticket Settings --}}
        <a href="{{ route('settings.tickets') }}" 
           class="group bg-white dark:bg-neutral-800 rounded-lg shadow-md hover:shadow-xl transition-all duration-500 hover:-translate-y-2 hover:scale-[1.02] border border-neutral-200 dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 overflow-hidden">
            <div class="p-6">
                <div class="flex items-start space-x-3">
                    <div class="relative">
                        <div class="h-10 w-10 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center group-hover:scale-110 group-hover:-rotate-3 transition-all duration-500">
                            <x-heroicon-o-ticket class="h-5 w-5 text-neutral-600 dark:text-neutral-400" />
                        </div>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
                            Tickets
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                            Workflow, priorities, statuses, and attachment settings
                        </p>
                        
                        <div class="flex items-center text-blue-600 dark:text-blue-400 opacity-0 group-hover:opacity-100 transition-all duration-500 transform group-hover:translate-x-2">
                            <span class="text-sm font-medium">Configure</span>
                            <x-heroicon-o-arrow-right class="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300" />
                        </div>
                    </div>
                </div>
            </div>
        </a>

        {{-- Organization Settings --}}
        <a href="{{ route('settings.organization') }}" 
           class="group bg-white dark:bg-neutral-800 rounded-lg shadow-md hover:shadow-xl transition-all duration-500 hover:-translate-y-2 hover:scale-[1.02] border border-neutral-200 dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 overflow-hidden">
            <div class="p-6">
                <div class="flex items-start space-x-3">
                    <div class="relative">
                        <div class="h-10 w-10 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
                            <x-heroicon-o-building-office-2 class="h-5 w-5 text-neutral-600 dark:text-neutral-400" />
                        </div>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
                            Organization
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                            Client organization management and policies
                        </p>
                        
                        <div class="flex items-center text-blue-600 dark:text-blue-400 opacity-0 group-hover:opacity-100 transition-all duration-500 transform group-hover:translate-x-2">
                            <span class="text-sm font-medium">Configure</span>
                            <x-heroicon-o-arrow-right class="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300" />
                        </div>
                    </div>
                </div>
            </div>
        </a>

        {{-- Contract Settings --}}
        <a href="{{ route('settings.contracts') }}" 
           class="group bg-white dark:bg-neutral-800 rounded-lg shadow-md hover:shadow-xl transition-all duration-500 hover:-translate-y-2 hover:scale-[1.02] border border-neutral-200 dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 overflow-hidden">
            <div class="p-6">
                <div class="flex items-start space-x-3">
                    <div class="relative">
                        <div class="h-10 w-10 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center group-hover:scale-110 group-hover:-rotate-3 transition-all duration-500">
                            <x-heroicon-o-document-text class="h-5 w-5 text-neutral-600 dark:text-neutral-400" />
                        </div>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
                            Contracts
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                            Contract types, terms, and management settings
                        </p>
                        
                        <div class="flex items-center text-blue-600 dark:text-blue-400 opacity-0 group-hover:opacity-100 transition-all duration-500 transform group-hover:translate-x-2">
                            <span class="text-sm font-medium">Configure</span>
                            <x-heroicon-o-arrow-right class="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300" />
                        </div>
                    </div>
                </div>
            </div>
        </a>

        {{-- Hardware Settings --}}
        <a href="{{ route('settings.hardware') }}" 
           class="group bg-white dark:bg-neutral-800 rounded-lg shadow-md hover:shadow-xl transition-all duration-500 hover:-translate-y-2 hover:scale-[1.02] border border-neutral-200 dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 overflow-hidden">
            <div class="p-6">
                <div class="flex items-start space-x-3">
                    <div class="relative">
                        <div class="h-10 w-10 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
                            <x-heroicon-o-cpu-chip class="h-5 w-5 text-neutral-600 dark:text-neutral-400" />
                        </div>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
                            Hardware
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                            Hardware types, serial management, and tracking
                        </p>
                        
                        <div class="flex items-center text-blue-600 dark:text-blue-400 opacity-0 group-hover:opacity-100 transition-all duration-500 transform group-hover:translate-x-2">
                            <span class="text-sm font-medium">Configure</span>
                            <x-heroicon-o-arrow-right class="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300" />
                        </div>
                    </div>
                </div>
            </div>
        </a>

        {{-- Schedule Settings --}}
        <a href="{{ route('settings.schedule') }}" 
           class="group bg-white dark:bg-neutral-800 rounded-lg shadow-md hover:shadow-xl transition-all duration-500 hover:-translate-y-2 hover:scale-[1.02] border border-neutral-200 dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 overflow-hidden">
            <div class="p-6">
                <div class="flex items-start space-x-3">
                    <div class="relative">
                        <div class="h-10 w-10 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center group-hover:scale-110 group-hover:-rotate-3 transition-all duration-500">
                            <x-heroicon-o-calendar-days class="h-5 w-5 text-neutral-600 dark:text-neutral-400" />
                        </div>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
                            Schedule
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                            Calendar settings, event types, and scheduling
                        </p>
                        
                        <div class="flex items-center text-blue-600 dark:text-blue-400 opacity-0 group-hover:opacity-100 transition-all duration-500 transform group-hover:translate-x-2">
                            <span class="text-sm font-medium">Configure</span>
                            <x-heroicon-o-arrow-right class="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300" />
                        </div>
                    </div>
                </div>
            </div>
        </a>

        {{-- User Settings --}}
        <a href="{{ route('settings.users') }}" 
           class="group bg-white dark:bg-neutral-800 rounded-lg shadow-md hover:shadow-xl transition-all duration-500 hover:-translate-y-2 hover:scale-[1.02] border border-neutral-200 dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 overflow-hidden">
            <div class="p-6">
                <div class="flex items-start space-x-3">
                    <div class="relative">
                        <div class="h-10 w-10 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
                            <x-heroicon-o-users class="h-5 w-5 text-neutral-600 dark:text-neutral-400" />
                        </div>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
                            Users
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                            User management, roles, and permissions
                        </p>
                        
                        <div class="flex items-center text-blue-600 dark:text-blue-400 opacity-0 group-hover:opacity-100 transition-all duration-500 transform group-hover:translate-x-2">
                            <span class="text-sm font-medium">Configure</span>
                            <x-heroicon-o-arrow-right class="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300" />
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>


</div>