<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title ?? 'Dashboard' }} – Support Portal</title>

    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    @stack('styles')
</head>

<body class="bg-neutral-50 dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 min-h-screen flex flex-col font-sans antialiased">

    {{-- HEADER --}}
    <header class="bg-neutral-100/80 dark:bg-neutral-800/70 backdrop-blur border-b border-neutral-200 dark:border-neutral-700 z-50">
        <div class="mx-4 px-4 py-4 flex items-center justify-between max-w-7xl">
            <a href="{{ route('dashboard') }}"
                class="flex items-center text-2xl font-semibold text-neutral-800 dark:text-neutral-100">
                <x-heroicon-o-sparkles class="h-10 w-10 mr-2 stroke-1" />
                Support Portal
            </a>
            <div class="flex items-center space-x-4">
               <x-theme-toggle />

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="text-base text-red-400 dark:text-red-500 hover:bg-red-200 dark:hover:bg-red-600/50 px-4 py-2 rounded-lg transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- PAGE BODY --}}
    <div class="flex flex-1 overflow-hidden">
        <x-sidebar />

        {{-- MAIN CONTENT --}}

        <main class="flex-1 overflow-auto p-6">
            {{ $slot }}
        </main>
    </div>

    {{-- FOOTER --}}
    <footer class="bg-neutral-100/80 dark:bg-neutral-900/70 backdrop-blur">
        <div class="max-w-7xl mx-auto px-4 py-3 text-center text-sm text-neutral-500 dark:text-neutral-400">
            &copy; {{ date('Y') }} Hospitality Technology Maldives
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')
</body>

</html>
