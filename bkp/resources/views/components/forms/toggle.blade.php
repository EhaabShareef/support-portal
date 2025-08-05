@props([
    'name',
    'label' => '',
    'checked' => false,
])

<div class="flex items-center space-x-4">
    <input type="hidden" name="{{ $name }}" value="0" />
    <label for="{{ $name }}" class="flex items-center cursor-pointer">
        <div class="relative">
            <input id="{{ $name }}" name="{{ $name }}" type="checkbox" value="1"
                   {{ old($name, $checked) ? 'checked' : '' }}
                   class="sr-only peer" />
            <div class="w-12 h-7 bg-gray-500 rounded-full peer peer-checked:bg-green-500 transition duration-300"></div>
            <div class="absolute top-1 left-1 size-5 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-full peer-checked:bg-white"></div>
        </div>
        <span class="ml-3 text-sm font-semibold text-neutral-700 dark:text-neutral-300 uppercase">{{ $label }}</span>
    </label>
</div>