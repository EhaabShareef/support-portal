<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100">Schedule Settings</h2>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Configure weekend days and schedule event types</p>
        </div>
    </div>

    {{-- Weekend Days Configuration --}}
    <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Weekend Days</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Select which days are considered weekends for scheduling purposes</p>
                </div>
                <div class="flex items-center gap-2">
                    @if($weekendDaysChanged)
                        <span class="text-sm text-orange-600 dark:text-orange-400 flex items-center gap-1">
                            <x-heroicon-o-exclamation-triangle class="h-4 w-4" />
                            Unsaved changes
                        </span>
                    @endif
                    <button wire:click="saveWeekendDays" 
                        class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                        <x-heroicon-o-check class="h-4 w-4 mr-2" />
                        Save Changes
                    </button>
                    <button wire:click="resetWeekendDays" 
                        wire:confirm="Are you sure you want to reset weekend days to defaults (Saturday & Sunday)?"
                        class="inline-flex items-center px-3 py-2 bg-neutral-600 hover:bg-neutral-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                        <x-heroicon-o-arrow-path class="h-4 w-4 mr-2" />
                        Reset
                    </button>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                @foreach($this->daysOfWeek as $day => $label)
                    <label class="flex flex-col items-center p-4 border border-neutral-200 dark:border-neutral-600 rounded-lg cursor-pointer transition-all duration-200 {{ in_array($day, $weekendDays) ? 'bg-sky-50 dark:bg-sky-900/20 border-sky-300 dark:border-sky-700' : 'bg-white dark:bg-neutral-900/50 hover:bg-neutral-50 dark:hover:bg-neutral-800/50' }}">
                        <input type="checkbox" 
                               wire:model.live="weekendDays" 
                               value="{{ $day }}" 
                               class="sr-only">
                        <div class="text-center">
                            <div class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 mb-1">{{ $label }}</div>
                            <div class="flex items-center justify-center">
                                @if(in_array($day, $weekendDays))
                                    <x-heroicon-o-check-circle class="h-6 w-6 text-sky-600 dark:text-sky-400" />
                                @else
                                    <x-heroicon-o-minus-circle class="h-6 w-6 text-neutral-400" />
                                @endif
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('weekendDays') <span class="text-red-500 text-xs mt-2">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Schedule Event Types Section --}}
    <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Schedule Event Types</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Define different types of scheduled events with custom colors</p>
                </div>
                <button wire:click="createEventType" 
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    Add Event Type
                </button>
            </div>
        </div>
        
        <div class="p-6">
            @if($this->scheduleEventTypes->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($this->scheduleEventTypes as $eventType)
                        <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-600 rounded-lg p-4 hover:shadow-md transition-all duration-200">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="w-4 h-4 rounded-full border-2 border-neutral-200 dark:border-neutral-600" style="background-color: {{ $eventType->color }}"></div>
                                        <h4 class="font-medium text-neutral-800 dark:text-neutral-100 text-sm">{{ $eventType->name }}</h4>
                                        @if(!$eventType->is_active)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200">
                                                Inactive
                                            </span>
                                        @endif
                                    </div>
                                    @if($eventType->description)
                                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mb-2">{{ $eventType->description }}</p>
                                    @endif
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 font-mono">{{ $eventType->color }}</p>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <button wire:click="editEventType({{ $eventType->id }})" 
                                        class="text-neutral-500 hover:text-green-600 dark:hover:text-green-400 transition-colors p-1" 
                                        title="Edit">
                                        <x-heroicon-o-pencil-square class="h-4 w-4" />
                                    </button>
                                    <button wire:click="toggleEventTypeStatus({{ $eventType->id }})" 
                                        class="text-neutral-500 hover:text-yellow-600 dark:hover:text-yellow-400 transition-colors p-1" 
                                        title="{{ $eventType->is_active ? 'Disable' : 'Enable' }}">
                                        @if($eventType->is_active)
                                            <x-heroicon-o-eye-slash class="h-4 w-4" />
                                        @else
                                            <x-heroicon-o-eye class="h-4 w-4" />
                                        @endif
                                    </button>
                                    @if($confirmingEventTypeDelete === $eventType->id)
                                        <button wire:click="deleteEventType" 
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200 transition-colors p-1" 
                                            title="Confirm delete">
                                            <x-heroicon-o-check class="h-4 w-4" />
                                        </button>
                                        <button wire:click="cancelEventTypeDelete" 
                                            class="text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors p-1" 
                                            title="Cancel">
                                            <x-heroicon-o-x-mark class="h-4 w-4" />
                                        </button>
                                    @else
                                        <button wire:click="confirmDeleteEventType({{ $eventType->id }})" 
                                            class="text-neutral-500 hover:text-red-600 dark:hover:text-red-400 transition-colors p-1" 
                                            title="Delete">
                                            <x-heroicon-o-trash class="h-4 w-4" />
                                        </button>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between pt-3 border-t border-neutral-200 dark:border-neutral-600">
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">Sort Order: {{ $eventType->sort_order }}</span>
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">by {{ $eventType->creator?->name ?? 'System' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <x-heroicon-o-calendar-days class="mx-auto h-12 w-12 text-neutral-400" />
                    <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-300">No event types</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Create your first schedule event type to categorize scheduled events.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Schedule Integration Info --}}
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
        <div class="flex">
            <x-heroicon-o-information-circle class="h-5 w-5 text-green-400 mr-3 flex-shrink-0 mt-0.5" />
            <div class="text-sm text-green-800 dark:text-green-200">
                <p class="font-medium mb-1">Schedule Integration</p>
                <p>Weekend days are used by the schedule calendar to highlight non-working days. Event types provide color coding and categorization for scheduled events throughout the application.</p>
            </div>
        </div>
    </div>
</div>

{{-- Event Type Modal --}}
@if($showEventTypeModal)
<div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeEventTypeModal"></div>

        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form wire:submit="saveEventType">
                <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                            {{ $eventTypeEditMode ? 'Edit Event Type' : 'Add New Event Type' }}
                        </h3>
                        <button type="button" wire:click="closeEventTypeModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Name *</label>
                            <input type="text" wire:model="eventTypeForm.name" required
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            @error('eventTypeForm.name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Description</label>
                            <textarea wire:model="eventTypeForm.description" rows="3"
                                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"></textarea>
                            @error('eventTypeForm.description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Color *</label>
                            <div class="flex items-center gap-3">
                                <input type="color" wire:model.live="eventTypeForm.color" 
                                       class="w-16 h-10 border border-neutral-300 dark:border-neutral-600 rounded cursor-pointer">
                                <input type="text" wire:model.live="eventTypeForm.color" 
                                       class="flex-1 px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent font-mono"
                                       placeholder="#3b82f6">
                            </div>
                            @error('eventTypeForm.color') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Sort Order</label>
                            <input type="number" wire:model="eventTypeForm.sort_order" min="0"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            @error('eventTypeForm.sort_order') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="flex flex-grow flex-col">
                                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Active</span>
                                <span class="text-sm text-neutral-500 dark:text-neutral-400">Make this event type available for scheduling</span>
                            </span>
                            <button type="button" wire:click="$toggle('eventTypeForm.is_active')" 
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 {{ $eventTypeForm['is_active'] ? 'bg-green-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                                <span class="sr-only">Active</span>
                                <span aria-hidden="true" 
                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $eventTypeForm['is_active'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>

                        {{-- Color Preview --}}
                        <div class="p-4 bg-neutral-50 dark:bg-neutral-900/50 rounded-lg">
                            <div class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Preview:</div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded-full border-2 border-neutral-200 dark:border-neutral-600" style="background-color: {{ $eventTypeForm['color'] }}"></div>
                                <span class="text-sm text-neutral-800 dark:text-neutral-100">{{ $eventTypeForm['name'] ?: 'Event Type Name' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <x-heroicon-o-check class="h-4 w-4 mr-2" />
                        {{ $eventTypeEditMode ? 'Update' : 'Create' }}
                    </button>
                    <button type="button" wire:click="closeEventTypeModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Unsaved Changes Warning --}}
@if($weekendDaysChanged)
<script>
window.addEventListener('beforeunload', function (e) {
    e.preventDefault();
    e.returnValue = 'You have unsaved changes to weekend days. Are you sure you want to leave?';
});
</script>
@endif