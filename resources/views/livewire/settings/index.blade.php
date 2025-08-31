<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
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
           class="group bg-white dark:bg-neutral-800 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-1 hover:scale-[1.02] border border-neutral-200 dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
            <div class="p-6">
                <div class="flex items-start space-x-3">
                    <x-heroicon-o-cog-6-tooth class="h-8 w-8 text-sky-600 dark:text-sky-400 flex-shrink-0 group-hover:scale-110 transition-transform duration-300" />
                    
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors duration-200">
                            General
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                            System preferences, themes, and basic configuration
                        </p>
                        
                        <div class="flex items-center text-sky-600 dark:text-sky-400 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:translate-x-1">
                            <span class="text-sm font-medium">Configure</span>
                            <x-heroicon-o-arrow-right class="ml-2 h-4 w-4" />
                        </div>
                    </div>
                </div>
            </div>
        </a>

        {{-- Ticket Settings --}}
        <a href="{{ route('settings.tickets') }}" 
           class="group bg-white dark:bg-neutral-800 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-1 hover:scale-[1.02] border border-neutral-200 dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
            <div class="p-6">
                <div class="flex items-start space-x-3">
                    <x-heroicon-o-ticket class="h-8 w-8 text-sky-600 dark:text-sky-400 flex-shrink-0 group-hover:scale-110 transition-transform duration-300" />
                    
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors duration-200">
                            Tickets
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                            Workflow, priorities, statuses, and attachment settings
                        </p>
                        
                        <div class="flex items-center text-sky-600 dark:text-sky-400 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:translate-x-1">
                            <span class="text-sm font-medium">Configure</span>
                            <x-heroicon-o-arrow-right class="ml-2 h-4 w-4" />
                        </div>
                    </div>
                </div>
            </div>
        </a>

        {{-- Organization Settings --}}
        <a href="{{ route('settings.organization') }}" 
           class="group bg-white dark:bg-neutral-800 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-1 hover:scale-[1.02] border border-neutral-200 dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
            <div class="p-6">
                <div class="flex items-start space-x-3">
                    <x-heroicon-o-building-office-2 class="h-8 w-8 text-sky-600 dark:text-sky-400 flex-shrink-0 group-hover:scale-110 transition-transform duration-300" />
                    
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors duration-200">
                            Organization
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                            Client organization management and policies
                        </p>
                        
                        <div class="flex items-center text-sky-600 dark:text-sky-400 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:translate-x-1">
                            <span class="text-sm font-medium">Configure</span>
                            <x-heroicon-o-arrow-right class="ml-2 h-4 w-4" />
                        </div>
                    </div>
                </div>
            </div>
        </a>

        {{-- Contract Settings --}}
        <a href="{{ route('settings.contracts') }}" 
           class="group bg-white dark:bg-neutral-800 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-1 hover:scale-[1.02] border border-neutral-200 dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
            <div class="p-6">
                <div class="flex items-start space-x-3">
                    <x-heroicon-o-document-text class="h-8 w-8 text-sky-600 dark:text-sky-400 flex-shrink-0 group-hover:scale-110 transition-transform duration-300" />
                    
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors duration-200">
                            Contracts
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                            Contract types, terms, and management settings
                        </p>
                        
                        <div class="flex items-center text-sky-600 dark:text-sky-400 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:translate-x-1">
                            <span class="text-sm font-medium">Configure</span>
                            <x-heroicon-o-arrow-right class="ml-2 h-4 w-4" />
                        </div>
                    </div>
                </div>
            </div>
        </a>

        {{-- Hardware Settings --}}
        <a href="{{ route('settings.hardware') }}" 
           class="group bg-white dark:bg-neutral-800 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-1 hover:scale-[1.02] border border-neutral-200 dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
            <div class="p-6">
                <div class="flex items-start space-x-3">
                    <x-heroicon-o-cpu-chip class="h-8 w-8 text-sky-600 dark:text-sky-400 flex-shrink-0 group-hover:scale-110 transition-transform duration-300" />
                    
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors duration-200">
                            Hardware
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                            Hardware types, serial management, and tracking
                        </p>
                        
                        <div class="flex items-center text-sky-600 dark:text-sky-400 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:translate-x-1">
                            <span class="text-sm font-medium">Configure</span>
                            <x-heroicon-o-arrow-right class="ml-2 h-4 w-4" />
                        </div>
                    </div>
                </div>
            </div>
        </a>

        {{-- Schedule Settings --}}
        <a href="{{ route('settings.schedule') }}" 
           class="group bg-white dark:bg-neutral-800 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-1 hover:scale-[1.02] border border-neutral-200 dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
            <div class="p-6">
                <div class="flex items-start space-x-3">
                    <x-heroicon-o-calendar-days class="h-8 w-8 text-sky-600 dark:text-sky-400 flex-shrink-0 group-hover:scale-110 transition-transform duration-300" />
                    
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors duration-200">
                            Schedule
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                            Calendar settings, event types, and scheduling
                        </p>
                        
                        <div class="flex items-center text-sky-600 dark:text-sky-400 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:translate-x-1">
                            <span class="text-sm font-medium">Configure</span>
                            <x-heroicon-o-arrow-right class="ml-2 h-4 w-4" />
                        </div>
                    </div>
                </div>
            </div>
        </a>

        {{-- User Settings --}}
        <a href="{{ route('settings.users') }}" 
           class="group bg-white dark:bg-neutral-800 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-1 hover:scale-[1.02] border border-neutral-200 dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
            <div class="p-6">
                <div class="flex items-start space-x-3">
                    <x-heroicon-o-users class="h-8 w-8 text-sky-600 dark:text-sky-400 flex-shrink-0 group-hover:scale-110 transition-transform duration-300" />
                    
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors duration-200">
                            Users
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                            User management, roles, and permissions
                        </p>
                        
                        <div class="flex items-center text-sky-600 dark:text-sky-400 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:translate-x-1">
                            <span class="text-sm font-medium">Configure</span>
                            <x-heroicon-o-arrow-right class="ml-2 h-4 w-4" />
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- Quick Actions Footer --}}
    <div class="bg-white/40 dark:bg-neutral-800/40 backdrop-blur border border-neutral-200/50 dark:border-neutral-700/50 rounded-xl p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="h-8 w-8 bg-gradient-to-br from-sky-400 to-blue-500 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-light-bulb class="h-4 w-4 text-white" />
                </div>
                <div>
                    <h3 class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Need Help?</h3>
                    <p class="text-xs text-neutral-600 dark:text-neutral-400">Explore our documentation for detailed configuration guides</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button class="inline-flex items-center px-3 py-2 bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-300 text-sm font-medium rounded-lg transition-all duration-200">
                    <x-heroicon-o-question-mark-circle class="h-4 w-4 mr-2" />
                    Help
                </button>
                <button class="inline-flex items-center px-3 py-2 bg-sky-100 dark:bg-sky-900/40 hover:bg-sky-200 dark:hover:bg-sky-900/60 text-sky-700 dark:text-sky-300 text-sm font-medium rounded-lg transition-all duration-200">
                    <x-heroicon-o-document-text class="h-4 w-4 mr-2" />
                    Documentation
                </button>
            </div>
        </div>
    </div>
</div>
