<div>
<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                    <x-heroicon-o-calendar-days class="h-8 w-8" />
                    Schedule Calendar
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">View team schedules and activities</p>
            </div>

            {{-- Month Navigation and Actions --}}
            <div class="flex items-center gap-2">
                <button wire:click="previousMonth" 
                        class="inline-flex items-center px-3 py-2 bg-neutral-200 dark:bg-neutral-700 hover:bg-neutral-300 dark:hover:bg-neutral-600 text-neutral-800 dark:text-neutral-100 rounded-md transition-all duration-200">
                    <x-heroicon-o-chevron-left class="h-4 w-4" />
                </button>
                
                <div class="px-4 py-2 bg-sky-100 dark:bg-sky-900/40 text-sky-800 dark:text-sky-300 rounded-md font-medium">
                    {{ $this->monthName }}
                </div>
                
                <button wire:click="nextMonth" 
                        class="inline-flex items-center px-3 py-2 bg-neutral-200 dark:bg-neutral-700 hover:bg-neutral-300 dark:hover:bg-neutral-600 text-neutral-800 dark:text-neutral-100 rounded-md transition-all duration-200">
                    <x-heroicon-o-chevron-right class="h-4 w-4" />
                </button>
                
                <button wire:click="goToToday" 
                        class="inline-flex items-center px-3 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    Today
                </button>

                {{-- Create Schedule Event Button (Admin only) --}}
                @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin']))
                <button wire:click="createScheduleEvent" 
                        type="button"
                        class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-all duration-200 ml-2">
                    <x-heroicon-o-plus class="h-4 w-4 mr-1" />
                    Create Event
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Department Group</label>
                <select wire:model.live="selectedDepartmentGroup" 
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">All Groups</option>
                    @foreach($this->departmentGroups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Event Type</label>
                <select wire:model.live="selectedEventType" 
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">All Types</option>
                    @foreach($this->eventTypes as $eventType)
                        <option value="{{ $eventType->id }}">{{ $eventType->label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Calendar Grid --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full">
                {{-- Calendar Header (Days 1-31) --}}
                <thead class="bg-neutral-100 dark:bg-neutral-800 sticky top-0 z-10">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider bg-neutral-200 dark:bg-neutral-700 sticky left-0 z-20">
                            User
                        </th>
                        @for($day = 1; $day <= $this->daysInMonth; $day++)
                            <th class="px-2 py-2 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider min-w-16">
                                {{ $day }}
                                <div class="text-xs text-neutral-400 font-normal">
                                    {{ $this->currentDate->day($day)->format('D') }}
                                </div>
                            </th>
                        @endfor
                    </tr>
                </thead>

                {{-- Calendar Body (Users grouped by Department Group) --}}
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse($this->users as $groupName => $groupUsers)
                        {{-- Department Group Header --}}
                        <tr class="bg-neutral-50 dark:bg-neutral-800/50">
                            <td colspan="{{ $this->daysInMonth + 1 }}" class="px-3 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                {{ $groupName ?: 'No Department Group' }}
                            </td>
                        </tr>

                        {{-- Users in this group --}}
                        @foreach($groupUsers as $user)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30">
                                <td class="px-3 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-neutral-50 dark:bg-neutral-800/50 sticky left-0 z-10">
                                    <div>
                                        <div>{{ $user->name }}</div>
                                        <div class="text-xs text-neutral-500">{{ $user->department?->name }}</div>
                                    </div>
                                </td>

                                @for($day = 1; $day <= $this->daysInMonth; $day++)
                                    <td class="px-1 py-2 text-center align-top min-h-16" style="min-height: 64px;">
                                        <div class="flex flex-col gap-1 min-h-12">
                                            @php
                                                $daySchedules = $this->getSchedulesForUserAndDay($user->id, $day);
                                            @endphp
                                            
                                            @foreach($daySchedules as $schedule)
                                                <div class="inline-block px-1 rounded text-xs text-white {{ $schedule->eventType->color }} event-badge">
                                                    {{ $schedule->eventType->code }}
                                                    <div class="tooltip">
                                                        <strong>{{ $schedule->eventType->label }}</strong>
                                                        @if($schedule->remarks)
                                                            <br>{{ $schedule->remarks }}
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach

                                            @if($daySchedules->count() > 2)
                                                <div class="text-xs text-neutral-500">
                                                    +{{ $daySchedules->count() - 2 }} more
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="{{ $this->daysInMonth + 1 }}" class="px-3 py-8 text-center text-neutral-500 dark:text-neutral-400">
                                No users found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Legend --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 mb-4">Event Types</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            @foreach($this->eventTypes as $eventType)
                <div class="flex items-center gap-2">
                    <span class="inline-block px-2 py-1 rounded text-xs text-white {{ $eventType->color }}">
                        {{ $eventType->code }}
                    </span>
                    <span class="text-sm text-neutral-700 dark:text-neutral-300">{{ $eventType->label }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Custom scrollbar styling */
    .custom-scrollbar::-webkit-scrollbar {
        height: 6px;
        width: 6px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.05);
        border-radius: 10px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(0, 0, 0, 0.3);
    }
    
    .dark .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
    }
    
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    /* Event hover tooltip styling */
    .event-badge {
        position: relative;
        cursor: help;
    }
    
    .event-badge .tooltip {
        visibility: hidden;
        opacity: 0;
        position: absolute;
        z-index: 1000;
        bottom: 125%;
        left: 50%;
        transform: translateX(-50%);
        background-color: rgba(0, 0, 0, 0.9);
        color: white;
        text-align: center;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 12px;
        line-height: 1.3;
        white-space: nowrap;
        max-width: 200px;
        word-wrap: break-word;
        white-space: normal;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    
    .event-badge .tooltip::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: rgba(0, 0, 0, 0.9) transparent transparent transparent;
    }
    
    .event-badge:hover .tooltip {
        visibility: visible;
        opacity: 1;
    }
</style>
@endpush

@push('scripts')
<script>
    // Debug modal functionality
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Schedule calendar loaded');
    });

    // Listen for modal events
    Livewire.on('modal-opened', function() {
        console.log('Modal opened event received');
    });
</script>
@endpush


{{-- Schedule Event Modal --}}
@if(isset($showScheduleModal) && $showScheduleModal)
<div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeScheduleModal"></div>
        
        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <form wire:submit="saveScheduleEvent">
                <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                            {{ $scheduleEditMode ? 'Edit Schedule Event' : 'Create Schedule Event' }}
                        </h3>
                        <button type="button" wire:click="closeScheduleModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        {{-- User Selection --}}
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">User *</label>
                            <select wire:model="scheduleForm.user_id" 
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500">
                                <option value="">Select a user...</option>
                                @foreach($this->allUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->department?->name }}</option>
                                @endforeach
                            </select>
                            @error('scheduleForm.user_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Event Type Selection --}}
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Event Type *</label>
                            <select wire:model="scheduleForm.event_type_id" 
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                <option value="">Select an event type...</option>
                                @foreach($this->eventTypes as $eventType)
                                    <option value="{{ $eventType->id }}" 
                                            data-color="{{ $eventType->color }}" 
                                            data-code="{{ $eventType->code }}">
                                        {{ $eventType->code }} - {{ $eventType->label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('scheduleForm.event_type_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            
                            {{-- Show selected event type preview --}}
                            @if(!empty($scheduleForm['event_type_id']))
                                @php
                                    $selectedEventType = $this->eventTypes->find($scheduleForm['event_type_id']);
                                @endphp
                                @if($selectedEventType)
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="text-sm text-neutral-600 dark:text-neutral-400">Selected:</span>
                                        <span class="inline-block px-2 py-1 rounded text-xs text-white font-medium {{ $selectedEventType->color }}">
                                            {{ $selectedEventType->code }}
                                        </span>
                                        <span class="text-sm text-neutral-700 dark:text-neutral-300">{{ $selectedEventType->label }}</span>
                                    </div>
                                @endif
                            @endif
                        </div>

                        {{-- Date Range --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Start Date *</label>
                                <input type="date" wire:model="scheduleForm.start_date" 
                                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                @error('scheduleForm.start_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">End Date *</label>
                                <input type="date" wire:model="scheduleForm.end_date" 
                                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                @error('scheduleForm.end_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Remarks --}}
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Remarks</label>
                            <textarea wire:model="scheduleForm.remarks" rows="3" 
                                      placeholder="Optional remarks or notes about this schedule event..."
                                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"></textarea>
                            @error('scheduleForm.remarks') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ $scheduleEditMode ? 'Update' : 'Create' }}
                    </button>
                    <button type="button" wire:click="closeScheduleModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
</div>