<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title ?? 'Dashboard' }} – Support Portal</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    @stack('styles')
</head>

<body class="bg-neutral-50 dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 min-h-screen font-sans antialiased">

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
