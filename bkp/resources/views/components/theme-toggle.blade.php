<div>
<button
    onclick="toggleTheme()"
    class="px-3 py-2 rounded text-neutral-900 dark:text-neutral-100 transition "
    title="Toggle theme"
    type="button"
>
    <span class="dark:hidden">
        <!-- Moon icon (Heroicon solid) -->
        <x-heroicon-o-moon class="size-6 stroke-1 hover:stroke-2 inline" />
    </span>
    <span class="hidden dark:inline">
        <!-- Sun icon (Heroicon solid) -->
        <x-heroicon-o-sun class="size-6 stroke-1 hover:stroke-2 inline" />
    </span>
</button>
</div>