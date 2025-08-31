<div class="space-y-6">
    {{-- Header Section --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                    <x-heroicon-o-cog-6-tooth class="h-8 w-8" />
                    Settings
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Configure system settings and preferences</p>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" 
             x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2"
            class="bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 p-4 rounded-lg shadow">
            <div class="flex items-center">
                <x-heroicon-o-check-circle class="h-5 w-5 mr-2" />
                {{ session('message') }}
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" 
             x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2"
            class="bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 p-4 rounded-lg shadow">
            <div class="flex items-center">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 mr-2" />
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Main Settings Layout --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg shadow-md">
        <div class="lg:grid lg:grid-cols-4 lg:gap-0">
            {{-- Vertical Tab Navigation (Desktop) / Horizontal (Mobile) --}}
            <div class="lg:col-span-1 lg:border-r lg:border-neutral-200 lg:dark:border-neutral-700">
                {{-- Desktop Vertical Tabs --}}
                <nav class="hidden lg:block p-6 space-y-2" aria-label="Settings sections">
                    @foreach($this->tabs as $tabKey => $tab)
                        <button wire:click="setActiveTab('{{ $tabKey }}')"
                            class="w-full text-left p-3 rounded-lg transition-all duration-200 group
                                {{ $activeTab === $tabKey 
                                    ? 'bg-sky-50 dark:bg-sky-900/20 text-sky-700 dark:text-sky-300 border-l-4 border-sky-500' 
                                    : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 hover:text-neutral-900 dark:hover:text-neutral-100' }}"
                            aria-selected="{{ $activeTab === $tabKey ? 'true' : 'false' }}"
                            aria-controls="panel-{{ $tabKey }}"
                            role="tab">
                            <div class="flex items-center gap-3">
                                <x-dynamic-component :component="$tab['icon']" 
                                    class="h-5 w-5 {{ $activeTab === $tabKey ? 'text-sky-600 dark:text-sky-400' : 'text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-300' }}" />
                                <div>
                                    <div class="font-medium text-sm">{{ $tab['label'] }}</div>
                                    <div class="text-xs text-neutral-500 dark:text-neutral-500 mt-0.5">{{ $tab['description'] }}</div>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </nav>

                {{-- Mobile Horizontal Tabs --}}
                <div class="lg:hidden border-b border-neutral-200 dark:border-neutral-700">
                    <nav class="flex overflow-x-auto py-4 px-6 space-x-4" aria-label="Settings sections">
                        @foreach($this->tabs as $tabKey => $tab)
                            <button wire:click="setActiveTab('{{ $tabKey }}')"
                                class="flex-shrink-0 p-2 rounded-lg transition-all duration-200
                                    {{ $activeTab === $tabKey 
                                        ? 'bg-sky-50 dark:bg-sky-900/20 text-sky-700 dark:text-sky-300' 
                                        : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-800/50' }}"
                                aria-selected="{{ $activeTab === $tabKey ? 'true' : 'false' }}"
                                role="tab">
                                <div class="flex items-center gap-2">
                                    <x-dynamic-component :component="$tab['icon']" class="h-5 w-5" />
                                    <span class="font-medium text-sm whitespace-nowrap">{{ $tab['label'] }}</span>
                                </div>
                            </button>
                        @endforeach
                    </nav>
                </div>
            </div>

            {{-- Tab Content Panel --}}
            <div class="lg:col-span-3">
                <div class="p-6 min-h-[500px]">
                    @foreach($this->tabs as $tabKey => $tab)
                        @if($activeTab === $tabKey)
                            <div id="panel-{{ $tabKey }}" 
                                 role="tabpanel" 
                                 aria-labelledby="tab-{{ $tabKey }}"
                                 x-data="{ show: true }"
                                 x-show="show"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform translate-x-4"
                                 x-transition:enter-end="opacity-100 transform translate-x-0">
                                @livewire($tab['component'], key($tabKey))
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Keyboard Navigation Support --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('[role="tab"]');
    
    tabButtons.forEach((button, index) => {
        button.addEventListener('keydown', function(e) {
            let nextIndex;
            
            switch(e.key) {
                case 'ArrowRight':
                case 'ArrowDown':
                    e.preventDefault();
                    nextIndex = (index + 1) % tabButtons.length;
                    break;
                case 'ArrowLeft':
                case 'ArrowUp':
                    e.preventDefault();
                    nextIndex = index === 0 ? tabButtons.length - 1 : index - 1;
                    break;
                case 'Home':
                    e.preventDefault();
                    nextIndex = 0;
                    break;
                case 'End':
                    e.preventDefault();
                    nextIndex = tabButtons.length - 1;
                    break;
                default:
                    return;
            }
            
            if (typeof nextIndex !== 'undefined') {
                tabButtons[nextIndex].focus();
                tabButtons[nextIndex].click();
            }
        });
    });
});
</script>