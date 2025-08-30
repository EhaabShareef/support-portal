@props([
    'show' => false,
    'title' => '',
    'size' => 'md', // sm, md, lg, xl, 2xl
    'closeButton' => true,
    'closeOnClickOutside' => true,
    'closeOnEscape' => true,
])

@php
    $sizeClasses = [
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        '2xl' => 'max-w-6xl',
    ];
@endphp

<div 
    x-data="{ 
        show: @js($show),
        close() {
            this.show = false;
            $dispatch('modal-closed');
        }
    }"
    x-show="show"
    x-init="
        @if($closeOnEscape)
            $watch('show', value => {
                if (value) {
                    document.addEventListener('keydown', handleEscape);
                } else {
                    document.removeEventListener('keydown', handleEscape);
                }
            });
            
            function handleEscape(e) {
                if (e.key === 'Escape') {
                    close();
                }
            }
        @endif
    "
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div 
        class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity"
        @if($closeOnClickOutside) @click="close()" @endif
    ></div>

    {{-- Modal --}}
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative transform overflow-hidden rounded-lg bg-white dark:bg-neutral-800 text-left shadow-xl transition-all w-full {{ $sizeClasses[$size] }}"
            @click.stop
        >
            {{-- Header --}}
            @if($title || $closeButton)
                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
                    @if($title)
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                            {{ $title }}
                        </h3>
                    @endif
                    
                    @if($closeButton)
                        <button 
                            @click="close()"
                            type="button"
                            class="rounded-md bg-white dark:bg-neutral-800 text-neutral-400 hover:text-neutral-500 dark:hover:text-neutral-300 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2"
                        >
                            <span class="sr-only">Close</span>
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                        </button>
                    @endif
                </div>
            @endif

            {{-- Content --}}
            <div class="px-6 py-4">
                {{ $slot }}
            </div>

            {{-- Footer --}}
            @if(isset($footer))
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-700/50">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
