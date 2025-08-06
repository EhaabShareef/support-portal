<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                    <x-heroicon-o-ticket class="h-8 w-8" />
                    Create Support Ticket
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Create a new support ticket</p>
            </div>

            <a href="{{ route('tickets.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-neutral-200 dark:bg-neutral-700 hover:bg-neutral-300 dark:hover:bg-neutral-600 text-sm text-neutral-800 dark:text-neutral-100 rounded-md transition-all duration-200">
                <x-heroicon-o-arrow-left class="h-4 w-4 mr-1" />
                Back
            </a>
        </div>
    </div>

    {{-- Flash Message --}}
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

    {{-- Ticket Form --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2">
                <label for="subject" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Subject</label>
                <input type="text" 
                       wire:model.defer="form.subject" 
                       id="subject"
                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100" />
                @error('form.subject') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Type</label>
                <select wire:model.defer="form.type" 
                        id="type"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    @foreach($typeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('form.type') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
            </div>

            {{-- Client Selection (for Admins/Agents only) --}}
            @if(auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Agent'))
            <div>
                <label for="client" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Client</label>
                <select wire:model.defer="form.client_id" 
                        id="client"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    <option value="">Select Client</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->organization?->name }})</option>
                    @endforeach
                </select>
                @error('form.client_id') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
            </div>
            @endif

            <div>
                <label for="organization" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Organization</label>
                <select wire:model.defer="form.organization_id" 
                        id="organization"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    <option value="">Select Organization</option>
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
                @error('form.organization_id') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <div>
                <label for="department" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Department</label>
                <select wire:model.defer="form.department_id" 
                        id="department"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    <option value="">Select Department</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
                @error('form.department_id') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <div>
                <label for="assigned_to" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Assigned To</label>
                <select wire:model.defer="form.assigned_to" 
                        id="assigned_to"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    <option value="">Unassigned</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('form.assigned_to') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <div>
                <label for="priority" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Priority</label>
                <select wire:model.defer="form.priority" 
                        id="priority"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    @foreach($priorityOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('form.priority') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
            </div>
        </div>

        {{-- Submit --}}
        <div class="pt-6 border-t border-neutral-200 dark:border-neutral-700 flex justify-end">
            <div class="relative">
                {{-- Normal Submit Button --}}
                <button wire:click="submit" 
                        wire:loading.remove 
                        wire:target="submit"
                        class="inline-flex items-center px-6 py-3 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
                    <x-heroicon-o-check class="w-5 h-5 mr-2" />
                    Submit Ticket
                </button>

                {{-- Loading State with Quirky Messages --}}
                <div wire:loading 
                     wire:target="submit"
                     class="inline-flex items-center px-6 py-3 bg-sky-500 text-white text-sm font-medium rounded-md shadow-sm cursor-not-allowed">
                    
                    {{-- Animated Thinking Dots --}}
                    <div class="flex space-x-1 mr-3">
                        <div class="w-2 h-2 bg-white rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>

                    {{-- Cycling Through Quirky Messages --}}
                    <div x-data="{ 
                        messages: [
                            '🤔 Hmm, let me think about this...',
                            '📝 Organizing your thoughts...',
                            '🧠 Processing the situation...',
                            '⚡ Adding some urgency...',
                            '🎯 Finding the right department...',
                            '🚀 Almost there...',
                            '✨ Making it perfect...'
                        ],
                        currentIndex: 0,
                        currentMessage: '🤔 Hmm, let me think about this...'
                    }"
                    x-init="
                        const interval = setInterval(() => {
                            currentIndex = (currentIndex + 1) % messages.length;
                            currentMessage = messages[currentIndex];
                        }, 800);
                        
                        // Clean up interval when element is removed
                        $el.addEventListener('removed', () => clearInterval(interval));
                    "
                    class="transition-all duration-300 ease-in-out">
                        <span x-text="currentMessage" class="min-w-0 truncate"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
