<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title ?? 'Dashboard' }} – Support Portal</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    @stack('styles')
</head>

<body class="bg-neutral-50 dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 min-h-screen font-sans antialiased">

    {{-- First-run Loading Overlay --}}
    @if(session('show_loading_overlay'))
        <x-loading-overlay />
        
        {{-- JS-off fallback for loading overlay --}}
        <noscript>
            <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-neutral-50/95 dark:bg-neutral-900/95 backdrop-blur-sm">
                <div class="text-center space-y-8 max-w-md mx-auto px-8">
                    <div class="flex justify-center">
                        <svg class="w-16 h-16 text-sky-600/80 dark:text-sky-400/80" 
                             viewBox="0 0 100 100" 
                             fill="none" 
                             xmlns="http://www.w3.org/2000/svg">
                            <rect x="25" y="25" width="50" height="50" 
                                  stroke="currentColor" 
                                  stroke-width="2" 
                                  fill="none" 
                                  rx="4"/>
                            <circle cx="50" cy="50" r="8" 
                                    stroke="currentColor" 
                                    stroke-width="2" 
                                    fill="none"/>
                        </svg>
                    </div>
                    <div class="space-y-3">
                        <p class="text-lg font-medium text-neutral-800 dark:text-neutral-200">
                            Signing you in…
                        </p>
                        <div class="flex justify-center space-x-1">
                            <div class="w-2 h-2 bg-sky-500/60 rounded-full"></div>
                            <div class="w-2 h-2 bg-sky-500/40 rounded-full"></div>
                            <div class="w-2 h-2 bg-sky-500/20 rounded-full"></div>
                        </div>
                    </div>
                </div>
            </div>
            <meta http-equiv="refresh" content="3;url={{ request()->url() }}">
        </noscript>
    @endif

    {{-- NAVIGATION --}}
    <x-navigation />

    {{-- MAIN CONTENT --}}
    <main class="min-h-screen bg-neutral-50 dark:bg-neutral-900">
        <div class="md:w-11/12 mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{ $slot }}
        </div>
    </main>

    {{-- Profile Modal Component --}}
    <livewire:user-profile />

    {{-- FOOTER (Optional) --}}
    <footer class="bg-white dark:bg-neutral-900 border-t border-neutral-200 dark:border-neutral-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="text-center text-sm text-neutral-500 dark:text-neutral-400">
                <p>&copy; {{ date('Y') }} Support Portal. Built with Laravel & Livewire.</p>
            </div>
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')

</body>

</html>
