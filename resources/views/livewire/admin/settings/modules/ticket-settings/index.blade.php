<x-settings.base-layout 
    :title="$this->getTitle()" 
    :description="$this->getDescription()" 
    :icon="$this->getIcon()"
    :hasUnsavedChanges="$hasUnsavedChanges"
>
    {{-- Progress Bar --}}
    @php
        $progressTracker = app(\App\Services\SettingsProgressTracker::class);
        $ticketProgress = $progressTracker->getModule('ticket');
    @endphp
    
    @if($ticketProgress)
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Implementation Progress</h3>
                <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ $ticketProgress['overall_progress'] }}% Complete</span>
            </div>
            <x-settings.progress-bar 
                :progress="$ticketProgress['overall_progress']" 
                :status="$ticketProgress['status']"
                :showPercentage="false"
                size="lg"
            />
        </div>
    @endif
    {{-- Section Navigation --}}
    <div class="border-b border-neutral-200 dark:border-neutral-700">
        <nav class="-mb-px flex space-x-8">
            @foreach($sections as $key => $section)
                <button
                    wire:click="setActiveSection('{{ $key }}')"
                    class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeSection === $key 
                        ? 'border-sky-500 text-sky-600 dark:text-sky-400' 
                        : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300' 
                    }}"
                >
                    <div class="flex items-center gap-2">
                        <x-dynamic-component :component="$section['icon']" class="h-4 w-4" />
                        {{ $section['title'] }}
                    </div>
                </button>
            @endforeach
        </nav>
    </div>

    {{-- Section Content --}}
    <div class="mt-6">
        @switch($activeSection)
            @case('workflow')
                @include('livewire.admin.settings.modules.ticket-settings.sections.workflow')
                @break
            @case('attachment')
                @include('livewire.admin.settings.modules.ticket-settings.sections.attachment')
                @break
            @case('priority')
                @include('livewire.admin.settings.modules.ticket-settings.sections.priority')
                @break
            @case('status')
                @include('livewire.admin.settings.modules.ticket-settings.sections.status')
                @break
        @endswitch
    </div>
</x-settings.base-layout>
