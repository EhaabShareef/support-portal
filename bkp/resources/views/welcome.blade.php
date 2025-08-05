<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Support Portal</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-neutral-100 dark:bg-neutral-900 text-neutral-800 dark:text-neutral-200 flex items-center justify-center min-h-screen p-4">

    <div class="fixed top-4 right-4 z-50">
        <x-theme-toggle />
    </div>

    <div class="max-w-lg text-center space-y-8">


        <h1 class="text-4xl font-semibold text-neutral-800 dark:text-neutral-100">
            Hospitality Technology Support Portal
        </h1>
        <p class="text-lg text-neutral-600 dark:text-neutral-300">
            Welcome! Submit and track your IT and service requests quickly and efficiently. Our team is here to help you
            with anything you want.
        </p>
        <a href="{{ route('login') }}"
            class="inline-block px-8 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md transition">
            Login to Your Account
        </a>
        <p class="text-xs text-neutral-500 dark:text-neutral-400">
            © {{ date('Y') }} Hospitality Technology Maldives. All rights reserved.
        </p>
    </div>
</body>

</html>
