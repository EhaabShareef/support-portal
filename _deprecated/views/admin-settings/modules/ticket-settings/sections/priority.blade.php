<x-settings.section title="Priority Configuration" description="Customize priority color schemes and behavior">
    <div class="space-y-6">
        {{-- Priority Colors --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Priority Colors</h4>
                <button 
                    wire:click="resetToDefaults"
                    class="px-3 py-1 text-sm bg-neutral-600 hover:bg-neutral-700 text-white rounded-md transition-colors duration-200"
                >
                    Reset to Defaults
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($this->getPriorities() as $priorityKey => $priority)
                    <div class="p-4 border border-neutral-200 dark:border-neutral-700 rounded-lg">
                        <div class="flex items-center gap-3 mb-3">
                            <x-dynamic-component :component="$priority['icon']" class="h-5 w-5 text-neutral-600 dark:text-neutral-400" />
                            <div>
                                <h5 class="font-medium text-neutral-800 dark:text-neutral-100">{{ $priority['name'] }}</h5>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $priority['description'] }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <input 
                                type="color" 
                                wire:model="priorityColors.{{ $priorityKey }}"
                                wire:change="updatePriorityColor('{{ $priorityKey }}', $event.target.value)"
                                class="h-8 w-12 rounded border-neutral-300 dark:border-neutral-600"
                            />
                            <input 
                                type="text" 
                                wire:model="priorityColors.{{ $priorityKey }}"
                                wire:change="updatePriorityColor('{{ $priorityKey }}', $event.target.value)"
                                class="flex-1 text-sm rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 focus:border-sky-500 focus:ring-sky-500"
                            />
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Priority Settings --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Custom Colors --}}
            <div>
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        wire:model="enableCustomColors" 
                        id="enableCustomColors"
                        class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-neutral-300 rounded"
                    />
                    <label for="enableCustomColors" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                        Enable Custom Priority Colors
                    </label>
                </div>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Allow custom colors for priority badges and indicators
                </p>
            </div>

            {{-- Priority Icons --}}
            <div>
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        wire:model="showPriorityIcons" 
                        id="showPriorityIcons"
                        class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-neutral-300 rounded"
                    />
                    <label for="showPriorityIcons" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                        Show Priority Icons
                    </label>
                </div>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Display icons alongside priority labels
                </p>
            </div>

            {{-- Priority Escalation --}}
            <div>
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        wire:model="enablePriorityEscalation" 
                        id="enablePriorityEscalation"
                        class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-neutral-300 rounded"
                    />
                    <label for="enablePriorityEscalation" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                        Enable Priority Escalation
                    </label>
                </div>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Automatically escalate tickets based on priority and time
                </p>
            </div>

            {{-- Escalation Delay --}}
            @if($enablePriorityEscalation)
            <div>
                <label for="escalationDelayHours" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    Escalation Delay (Hours)
                </label>
                <input 
                    type="number" 
                    wire:model="escalationDelayHours" 
                    id="escalationDelayHours"
                    min="1" 
                    max="168"
                    class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 focus:border-sky-500 focus:ring-sky-500"
                />
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Hours before a ticket is automatically escalated
                </p>
            </div>
            @endif
        </div>

        {{-- Priority Preview --}}
        <div class="mt-8">
            <h4 class="text-lg font-medium text-neutral-800 dark:text-neutral-100 mb-4">Priority Preview</h4>
            <div class="flex flex-wrap gap-2">
                @foreach($this->getPriorities() as $priorityKey => $priority)
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium"
                         style="background-color: {{ $priorityColors[$priorityKey] ?? '#3b82f6' }}; color: white;">
                        @if($showPriorityIcons)
                            <x-dynamic-component :component="$priority['icon']" class="h-3 w-3" />
                        @endif
                        {{ $priority['name'] }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-settings.section>
