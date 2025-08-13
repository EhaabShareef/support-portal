@props(['ticket', 'isMobile' => false])

@php
    $user = auth()->user();
    $iconSize = $isMobile ? 'h-4 w-4' : 'h-3 w-3';
    $buttonClass = $isMobile 
        ? 'inline-flex items-center px-2 py-1 text-xs rounded transition-all duration-200'
        : 'inline-flex items-center px-2 py-1 text-xs rounded transition-all duration-200';
@endphp

{{-- Quick Actions for Admin/Support --}}
@if($user->can('tickets.update'))
    @if($ticket->status === 'closed')
        {{-- Reopen Button (only if within reopen window or user is admin/support) --}}
        @php
            $canReopen = $user->hasRole(['admin', 'support']) || 
                       ($user->hasRole('client') && 
                        $user->organization_id === $ticket->organization_id &&
                        $ticket->closed_at && 
                        floor(now()->diffInDays($ticket->closed_at)) <= (\App\Models\Setting::get('tickets.reopen_window_days', 3)));
        @endphp
        @if($canReopen)
            <button wire:click="openReopenModal({{ $ticket->id }})" 
                    class="{{ $buttonClass }} text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30"
                    title="Reopen Ticket">
                <x-heroicon-o-arrow-path class="{{ $iconSize }}" />
            </button>
        @else
            <span class="{{ $buttonClass }} text-neutral-400 cursor-not-allowed" title="Reopen window expired">
                <x-heroicon-o-lock-closed class="{{ $iconSize }}" />
            </span>
        @endif
    @else
        {{-- Normal actions for non-closed tickets --}}
        
        {{-- Assign to Me (only for unassigned tickets) --}}
        @if(!$ticket->owner_id && ($user->hasRole('admin') || $user->hasRole('support')))
            <button wire:click="assignToMe({{ $ticket->id }})" 
                    class="{{ $buttonClass }} text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/30"
                    title="Assign to Me">
                <x-heroicon-o-user-plus class="{{ $iconSize }}" />
            </button>
        @endif

        {{-- Close Ticket --}}
        @if(in_array($ticket->status, ['solution_provided', 'in_progress', 'open']))
            <button wire:click="openCloseConfirmModal({{ $ticket->id }})" 
                    class="{{ $buttonClass }} text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30"
                    title="Close Ticket">
                <x-heroicon-o-x-circle class="{{ $iconSize }}" />
            </button>
        @endif

        {{-- Priority Dropdown (desktop only, admin only) --}}
        @if(!$isMobile && $user->hasRole('admin'))
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button @click="open = !open" 
                        class="{{ $buttonClass }} text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300 hover:bg-orange-50 dark:hover:bg-orange-900/30"
                        title="Change Priority">
                    <x-heroicon-o-flag class="{{ $iconSize }}" />
                </button>
                
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-1 w-24 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-md shadow-lg z-10">
                    <div class="py-1">
                        @foreach(['low', 'normal', 'high', 'urgent', 'critical'] as $priority)
                            @if($priority !== $ticket->priority)
                                <button onclick="confirmPriorityChange({{ $ticket->id }}, '{{ $priority }}', '{{ $ticket->priority }}', '{{ ucfirst($priority) }}')" 
                                        @click="open = false"
                                        class="w-full text-left px-2 py-1 text-xs text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700">
                                    {{ ucfirst($priority) }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endif
@endif