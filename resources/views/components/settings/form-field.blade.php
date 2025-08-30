@props([
    'type' => 'text',
    'label' => '',
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'help' => '',
    'options' => [],
    'min' => null,
    'max' => null,
    'step' => null,
    'required' => false,
    'disabled' => false,
    'wire:model' => null,
])

<div class="space-y-2">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    @switch($type)
        @case('select')
            <select 
                id="{{ $name }}"
                name="{{ $name }}"
                @if($wire:model) wire:model="{{ $wire:model }}" @endif
                @if($disabled) disabled @endif
                class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 focus:border-sky-500 focus:ring-sky-500 {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
            >
                @foreach($options as $value => $label)
                    <option value="{{ $value }}" {{ $value == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @break

        @case('textarea')
            <textarea 
                id="{{ $name }}"
                name="{{ $name }}"
                @if($wire:model) wire:model="{{ $wire:model }}" @endif
                @if($placeholder) placeholder="{{ $placeholder }}" @endif
                @if($disabled) disabled @endif
                rows="3"
                class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 focus:border-sky-500 focus:ring-sky-500 {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
            >{{ $value }}</textarea>
            @break

        @case('checkbox')
            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    id="{{ $name }}"
                    name="{{ $name }}"
                    @if($wire:model) wire:model="{{ $wire:model }}" @endif
                    @if($disabled) disabled @endif
                    class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-neutral-300 rounded {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
                />
                <label for="{{ $name }}" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                    {{ $label }}
                </label>
            </div>
            @break

        @case('number')
            <input 
                type="number" 
                id="{{ $name }}"
                name="{{ $name }}"
                @if($wire:model) wire:model="{{ $wire:model }}" @endif
                @if($value) value="{{ $value }}" @endif
                @if($placeholder) placeholder="{{ $placeholder }}" @endif
                @if($min) min="{{ $min }}" @endif
                @if($max) max="{{ $max }}" @endif
                @if($step) step="{{ $step }}" @endif
                @if($disabled) disabled @endif
                class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 focus:border-sky-500 focus:ring-sky-500 {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
            />
            @break

        @case('color')
            <div class="flex items-center space-x-3">
                <input 
                    type="color" 
                    id="{{ $name }}"
                    name="{{ $name }}"
                    @if($wire:model) wire:model="{{ $wire:model }}" @endif
                    @if($value) value="{{ $value }}" @endif
                    @if($disabled) disabled @endif
                    class="h-10 w-16 rounded border-neutral-300 dark:border-neutral-600 {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
                />
                <input 
                    type="text" 
                    @if($wire:model) wire:model="{{ $wire:model }}" @endif
                    @if($value) value="{{ $value }}" @endif
                    @if($placeholder) placeholder="{{ $placeholder }}" @endif
                    @if($disabled) disabled @endif
                    class="flex-1 rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 focus:border-sky-500 focus:ring-sky-500 {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
                />
            </div>
            @break

        @default
            <input 
                type="{{ $type }}" 
                id="{{ $name }}"
                name="{{ $name }}"
                @if($wire:model) wire:model="{{ $wire:model }}" @endif
                @if($value) value="{{ $value }}" @endif
                @if($placeholder) placeholder="{{ $placeholder }}" @endif
                @if($disabled) disabled @endif
                class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 focus:border-sky-500 focus:ring-sky-500 {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
            />
    @endswitch

    @if($help)
        <p class="text-sm text-neutral-500 dark:text-neutral-400">
            {{ $help }}
        </p>
    @endif

    @error($wire:model)
        <p class="text-sm text-red-600 dark:text-red-400">
            {{ $message }}
        </p>
    @enderror
</div>
