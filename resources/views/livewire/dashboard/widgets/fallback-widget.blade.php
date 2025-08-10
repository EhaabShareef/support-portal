<div class="h-full p-4 bg-white/5 dark:bg-neutral-800 backdrop-blur-md border border-white/20 rounded-lg shadow-md">
    <div class="flex flex-col items-center justify-center h-full space-y-3">
        <!-- Icon -->
        <x-heroicon-o-wrench-screwdriver class="w-10 h-10 text-amber-500" />

        <!-- Message -->
        <p class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 text-center">
            🚧 Widget still in beta brew...
        </p>
        <p class="text-sm text-neutral-600 dark:text-neutral-400 text-center">
            Our code gnomes are busy casting functions. 🧙‍♂️
        </p>
        
        @if($widgetName !== 'Unknown Widget')
            <div class="mt-2 px-3 py-1 bg-amber-500/10 rounded-full">
                <p class="text-xs text-amber-600 dark:text-amber-400 font-medium">
                    {{ $widgetName }} ({{ $widgetSize }})
                </p>
            </div>
        @endif
    </div>
</div>