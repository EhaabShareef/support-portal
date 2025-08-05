@props([
    'title' => 'Are you sure?',
    'message',
    'confirmAction',
    'cancelUrl',
    'confirmText' => 'Yes, Delete',
    'cancelText' => 'Cancel',
])

<div class="max-w-xl mx-auto mt-10 bg-white dark:bg-neutral-100 border border-red-200 dark:border-red-700 p-6 rounded-lg text-center">
    <x-heroicon-o-exclamation-triangle class="h-8 w-8 text-red-500 mx-auto mb-4" />
    <h2 class="text-lg font-bold text-red-600 mb-2">{{ $title }}</h2>
    <p class="text-sm text-neutral-500 dark:text-neutral-300 mb-6">
        {!! $message !!}
    </p>

    <div class="flex justify-center space-x-4">
        <a href="{{ $cancelUrl }}"
           class="px-4 py-2 bg-neutral-200 dark:bg-neutral-700 text-neutral-800 dark:text-white rounded-md text-sm hover:bg-neutral-300 dark:hover:bg-neutral-600">
            {{ $cancelText }}
        </a>

        <form action="{{ $confirmAction }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="px-4 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700">
                {{ $confirmText }}
            </button>
        </form>
    </div>
</div>