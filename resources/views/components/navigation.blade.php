{{-- Claude AI-inspired Navigation Component --}}
<nav x-data="{ 
    mobileMenuOpen: false, 
    collapsed: false,
    currentRoute: '{{ request()->route()->getName() }}' 
}" 
    class="sticky top-0 z-50 bg-white/95 dark:bg-neutral-900/95 backdrop-blur-sm border-b border-neutral-200/50 dark:border-neutral-700/50">
    
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            
            {{-- Left: Logo --}}
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center space-x-3 text-xl font-semibold text-neutral-900 dark:text-neutral-100 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors">
                    <x-heroicon-o-sparkles class="h-8 w-8 text-sky-600" />
                    <span class="hidden sm:block">Support Portal</span>
                </a>
            </div>

            {{-- Center: Main Navigation (Desktop) --}}
            <div class="hidden lg:flex items-center space-x-1">
                
                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}"
                   class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
                    <x-heroicon-o-home class="h-4 w-4" />
                    <span>Dashboard</span>
                </a>

                {{-- Tickets --}}
                <a href="{{ route('tickets.index') }}"
                   class="nav-link {{ request()->routeIs('tickets.*') ? 'nav-link-active' : '' }}">
                    <x-heroicon-o-ticket class="h-4 w-4" />
                    <span>Tickets</span>
                </a>

                {{-- Organizations --}}
                <a href="{{ route('organizations.index') }}"
                   class="nav-link {{ request()->routeIs('organizations.*') ? 'nav-link-active' : '' }}">
                    <x-heroicon-o-building-office-2 class="h-4 w-4" />
                    <span>Organizations</span>
                </a>

                {{-- Admin Users (only for admins) --}}
                @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.users.index') }}"
                   class="nav-link {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : '' }}">
                    <x-heroicon-o-users class="h-4 w-4" />
                    <span>Users</span>
                </a>
                
                {{-- Admin Settings (only for admins) --}}
                <a href="{{ route('admin.settings') }}"
                   class="nav-link {{ request()->routeIs('admin.settings') ? 'nav-link-active' : '' }}">
                    <x-heroicon-o-cog-6-tooth class="h-4 w-4" />
                    <span>Settings</span>
                </a>
                @endif

            </div>

            {{-- Right: User Actions --}}
            <div class="flex items-center space-x-3">
                
                {{-- Theme Toggle --}}
                <x-theme-toggle />

                {{-- User Menu --}}
                <div class="relative" x-data="{ userMenuOpen: false }">
                    <button @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center space-x-2 px-3 py-2 rounded-lg text-sm font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <div class="w-7 h-7 bg-sky-100 dark:bg-sky-900 rounded-full flex items-center justify-center">
                            <span class="text-xs font-semibold text-sky-700 dark:text-sky-300">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </span>
                        </div>
                        <span class="hidden sm:block">{{ auth()->user()->name ?? 'User' }}</span>
                        <x-heroicon-o-chevron-down class="h-4 w-4" />
                    </button>

                    {{-- User Dropdown --}}
                    <div x-show="userMenuOpen" 
                         @click.away="userMenuOpen = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 bg-white dark:bg-neutral-800 rounded-lg shadow-lg border border-neutral-200 dark:border-neutral-700 py-1">
                        
                        <div class="px-4 py-2 border-b border-neutral-200 dark:border-neutral-700">
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ auth()->user()->name ?? 'User' }}</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ auth()->user()->email ?? '' }}</p>
                            @if(auth()->user()->isAdmin())
                                <p class="text-xs text-sky-600 dark:text-sky-400">
                                    Role: Admin
                                </p>
                            @elseif(auth()->user()->roles->count() > 0)
                                <p class="text-xs text-sky-600 dark:text-sky-400">
                                    Role: {{ auth()->user()->roles->first()->name }}
                                </p>
                            @else
                                <p class="text-xs text-red-600 dark:text-red-400">No Role Assigned</p>
                            @endif
                        </div>
                        
                        <a href="#" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700">
                            <x-heroicon-o-user-circle class="h-4 w-4 inline mr-2" />
                            Profile
                        </a>
                        
                        <a href="#" class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700">
                            <x-heroicon-o-cog-6-tooth class="h-4 w-4 inline mr-2" />
                            Settings
                        </a>
                        
                        <div class="border-t border-neutral-200 dark:border-neutral-700 mt-1 pt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                    <x-heroicon-o-arrow-right-on-rectangle class="h-4 w-4 inline mr-2" />
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Mobile Menu Button --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="lg:hidden p-2 rounded-lg text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <x-heroicon-o-bars-3 x-show="!mobileMenuOpen" class="h-5 w-5" />
                    <x-heroicon-o-x-mark x-show="mobileMenuOpen" class="h-5 w-5" />
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform -translate-y-2"
             class="lg:hidden bg-white dark:bg-neutral-900 border-t border-neutral-200 dark:border-neutral-700">
            
            <div class="px-4 py-4 space-y-1">
                
                {{-- Mobile Dashboard --}}
                <a href="{{ route('dashboard') }}"
                   class="mobile-nav-link {{ request()->routeIs('dashboard') ? 'mobile-nav-link-active' : '' }}"
                   @click="mobileMenuOpen = false">
                    <x-heroicon-o-home class="h-5 w-5" />
                    <span>Dashboard</span>
                </a>

                {{-- Mobile Tickets --}}
                <a href="{{ route('tickets.index') }}"
                   class="mobile-nav-link {{ request()->routeIs('tickets.*') ? 'mobile-nav-link-active' : '' }}"
                   @click="mobileMenuOpen = false">
                    <x-heroicon-o-ticket class="h-5 w-5" />
                    <span>Tickets</span>
                </a>

                {{-- Mobile Organizations --}}
                <a href="{{ route('organizations.index') }}"
                   class="mobile-nav-link {{ request()->routeIs('organizations.*') ? 'mobile-nav-link-active' : '' }}"
                   @click="mobileMenuOpen = false">
                    <x-heroicon-o-building-office-2 class="h-5 w-5" />
                    <span>Organizations</span>
                </a>

                {{-- Mobile Admin Users --}}
                @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.users.index') }}"
                   class="mobile-nav-link {{ request()->routeIs('admin.users.*') ? 'mobile-nav-link-active' : '' }}"
                   @click="mobileMenuOpen = false">
                    <x-heroicon-o-users class="h-5 w-5" />
                    <span>Users</span>
                </a>
                
                {{-- Mobile Admin Settings --}}
                <a href="{{ route('admin.settings') }}"
                   class="mobile-nav-link {{ request()->routeIs('admin.settings') ? 'mobile-nav-link-active' : '' }}"
                   @click="mobileMenuOpen = false">
                    <x-heroicon-o-cog-6-tooth class="h-5 w-5" />
                    <span>Settings</span>
                </a>
                @endif

            </div>
        </div>
    </div>
</nav>