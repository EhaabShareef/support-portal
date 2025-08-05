@props([
    'name',
    'label' => null,
    'required' => false,
    'options' => [],
    'value' => '',
])

<div>
    @if ($label)
        <label for="{{ $name }}" class="block text-xs uppercase font-semibold text-neutral-500 dark:text-neutral-400 mb-2">
            {{ $label }}
            @if ($required)
                <span class="text-sm font-light ml-1 text-red-500">*</span>
            @endif
        </label>
    @endif

    <select
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'w-full mb-4 px-4 py-2 border border-neutral-300 dark:border-neutral-300 rounded-md bg-neutral-50 dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100']) }}>
        <option value="" disabled selected>Select {{ strtolower($label ?? $name) }}</option>
        @foreach ($options as $key => $option)
            <option value="{{ is_string($options) ? $option : $key }}"
                {{ old($name, $value) == (is_string($options) ? $option : $key) ? 'selected' : '' }}>
                {{ $option }}
            </option>
        @endforeach
    </select>

    @error($name)
        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>