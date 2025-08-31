<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                    <x-heroicon-o-arrow-path class="h-8 w-8" />
                    Ticket Workflow Settings
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Configure ticket workflow behavior and automation rules</p>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="resetToDefaults" 
                    wire:confirm="Are you sure you want to reset all workflow settings to their defaults? This cannot be undone."
                    class="inline-flex items-center px-3 py-2 bg-neutral-600 hover:bg-neutral-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-arrow-path class="h-4 w-4 mr-2" />
                    Reset All
                </button>
                <button wire:click="saveSettings" 
                    class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-check class="h-4 w-4 mr-2" />
                    Save Changes
                </button>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if($showFlash)
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" 
             x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2"
             class="p-4 rounded-lg shadow {{ $flashType === 'success' ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200' }}">
            <div class="flex items-center">
                @if($flashType === 'success')
                    <x-heroicon-o-check-circle class="h-5 w-5 mr-2" />
                @else
                    <x-heroicon-o-exclamation-triangle class="h-5 w-5 mr-2" />
                @endif
                <span>{{ $flashMessage }}</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Basic Workflow Settings --}}
        <div class="space-y-6">
            {{-- Default Status on Reply --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-8 w-8 bg-sky-100 dark:bg-sky-900/40 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-chat-bubble-left-right class="h-4 w-4 text-sky-600 dark:text-sky-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Default Status on Reply</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Set the default status when staff replies to tickets</p>
                    </div>
                </div>
                
                <select wire:model="defaultStatusOnReply" 
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
                    <option value="">Select default status...</option>
                    @foreach($statusOptions as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                @error('defaultStatusOnReply')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Reopen Window --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-8 w-8 bg-green-100 dark:bg-green-900/40 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-clock class="h-4 w-4 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Reopen Window</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Days clients can reopen closed tickets</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="relative flex items-center">
                        <input type="number" wire:model="reopenWindowDays" min="0" max="365"
                               class="w-20 px-3 py-2 pr-8 border border-neutral-300 dark:border-neutral-600 rounded-l-md bg-white dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200 text-center">
                        <div class="absolute right-0 top-0 bottom-0 flex flex-col border-l border-neutral-300 dark:border-neutral-600">
                            <button type="button" wire:click="$set('reopenWindowDays', {{ min(365, $reopenWindowDays + 1) }})"
                                    class="flex-1 px-1.5 py-0.5 bg-neutral-50 dark:bg-neutral-800 hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 transition-colors duration-200 border-b border-neutral-300 dark:border-neutral-600 rounded-tr-md">
                                <x-heroicon-o-chevron-up class="h-3 w-3" />
                            </button>
                            <button type="button" wire:click="$set('reopenWindowDays', {{ max(0, $reopenWindowDays - 1) }})"
                                    class="flex-1 px-1.5 py-0.5 bg-neutral-50 dark:bg-neutral-800 hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 transition-colors duration-200 rounded-br-md">
                                <x-heroicon-o-chevron-down class="h-3 w-3" />
                            </button>
                        </div>
                    </div>
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">days</span>
                </div>
                @error('reopenWindowDays')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Message Ordering --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-8 w-8 bg-purple-100 dark:bg-purple-900/40 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-list-bullet class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Message Ordering</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">How ticket messages are displayed</p>
                    </div>
                </div>
                
                <div class="space-y-2">
                    @foreach($messageOrderingOptions as $value => $label)
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" wire:model="messageOrdering" value="{{ $value }}"
                                   class="h-4 w-4 text-sky-600 border-neutral-300 focus:ring-sky-500">
                            <span class="text-sm text-neutral-700 dark:text-neutral-300">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('messageOrdering')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Priority Settings --}}
        <div class="space-y-6">
            {{-- Priority Hierarchy --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-8 w-8 bg-orange-100 dark:bg-orange-900/40 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-arrow-trending-up class="h-4 w-4 text-orange-600 dark:text-orange-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Priority Hierarchy</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Ticket priority levels in ascending order</p>
                    </div>
                </div>

                <div class="space-y-2">
                    @php
                        $priorities = [
                            'low' => ['icon' => 'heroicon-o-arrow-down-circle', 'color' => 'text-green-600 dark:text-green-400'],
                            'normal' => ['icon' => 'heroicon-o-arrow-path', 'color' => 'text-blue-600 dark:text-blue-400'],
                            'high' => ['icon' => 'heroicon-o-arrow-up-circle', 'color' => 'text-orange-600 dark:text-orange-400'],
                            'urgent' => ['icon' => 'heroicon-o-exclamation-triangle', 'color' => 'text-red-600 dark:text-red-400'],
                            'critical' => ['icon' => 'heroicon-o-fire', 'color' => 'text-purple-600 dark:text-purple-400'],
                        ];
                    @endphp
                    
                    @foreach($priorities as $priority => $config)
                        <div class="flex items-center gap-3 p-2 rounded-lg {{ $loop->last ? '' : 'border-b border-neutral-200 dark:border-neutral-700' }}">
                            <x-dynamic-component :component="$config['icon']" class="h-5 w-5 {{ $config['color'] }}" />
                            <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300 capitalize">{{ $priority }}</span>
                            @if(!$loop->last)
                                <x-heroicon-o-chevron-down class="h-4 w-4 text-neutral-400 ml-auto" />
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Client Escalation Settings --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-8 w-8 bg-red-100 dark:bg-red-900/40 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-exclamation-triangle class="h-4 w-4 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Client Escalation</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Allow clients to escalate after de-escalation</p>
                    </div>
                </div>

                {{-- Enable/Disable Toggle --}}
                <div class="flex items-center justify-between mb-4">
                    <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Allow Client Escalation</label>
                    <button type="button" wire:click="$toggle('allowClientEscalation')" 
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 {{ $allowClientEscalation ? 'bg-sky-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                        <span class="sr-only">Allow client escalation</span>
                        <span aria-hidden="true" 
                              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $allowClientEscalation ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>

                @if($allowClientEscalation)
                    {{-- Escalation Cooldown --}}
                    <div class="bg-neutral-50 dark:bg-neutral-800/50 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
                        <div class="flex items-center gap-3">
                            <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Cooldown Period</label>
                            <input type="number" wire:model="escalationCooldownHours" 
                                   min="1" max="168"
                                   class="w-20 px-2 py-1 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 focus:ring-1 focus:ring-sky-500 focus:border-transparent">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">hours</span>
                        </div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            Clients must wait this long after admin/agent de-escalation before they can escalate again
                        </p>
                        @error('escalationCooldownHours')
                            <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
