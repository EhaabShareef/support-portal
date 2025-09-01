<div class="space-y-6">
    {{-- Custom CSS for progress bar --}}
    <style>
        .progress-bar-container {
            position: relative;
            width: 100%;
            overflow: hidden;
        }
        .progress-bar-fill {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            transition: width 0.3s ease;
            max-width: 100% !important;
        }
    </style>

    {{-- Header --}}
    <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-800 dark:text-neutral-100">
                    Create Support Ticket
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                    @php
                        $totalSteps = $this->isHardwareDepartment() ? 3 : 2;
                        $displayStep = $this->isHardwareDepartment() ? $currentStep : ($currentStep === 3 ? 2 : $currentStep);
                    @endphp
                    Step {{ $displayStep }} of {{ $totalSteps }}: 
                    @if($currentStep === 1)
                        Basic Information
                    @elseif($currentStep === 2 && $this->isHardwareDepartment())
                        Hardware Selection
                    @else
                        Issue Details
                    @endif
                </p>
            </div>
            <a href="{{ route('tickets.index') }}" 
               class="px-4 py-2 bg-neutral-200 dark:bg-neutral-700 text-neutral-800 dark:text-neutral-100 rounded-md">
                Back
            </a>
        </div>
    </div>

    {{-- Progress Bar --}}
    <div class="bg-white dark:bg-neutral-800 rounded-lg p-4 shadow-md">
        <div class="progress-bar-container w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2">
            @php
                $totalSteps = $this->isHardwareDepartment() ? 3 : 2;
                $progressPercentage = min(max(($currentStep / $totalSteps) * 100, 0), 100);
            @endphp
            <div class="progress-bar-fill bg-sky-600 rounded-full" 
                 style="width: {{ $progressPercentage }}%;"></div>
        </div>
        <div class="flex justify-between mt-2 text-xs text-neutral-500">
            <span class="{{ $currentStep >= 1 ? 'text-sky-600 font-medium' : '' }} flex-shrink-0">Basic Information</span>
            @if($this->isHardwareDepartment())
                <span class="{{ $currentStep >= 2 ? 'text-sky-600 font-medium' : '' }} flex-shrink-0 px-2">Hardware Selection</span>
                <span class="{{ $currentStep >= 3 ? 'text-sky-600 font-medium' : '' }} flex-shrink-0">Issue Details</span>
            @else
                <span class="{{ $currentStep >= 2 ? 'text-sky-600 font-medium' : '' }} flex-shrink-0 ml-auto">Issue Details</span>
            @endif
        </div>
        <!-- Debug: Current step: {{ $currentStep }}, Is hardware: {{ $this->isHardwareDepartment() ? 'true' : 'false' }}, Progress: {{ $progressPercentage }}% -->
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 p-4 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 p-4 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- Step 1: Basic Information --}}
    @if($currentStep === 1)
    <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 shadow-md">
        <h2 class="text-lg font-semibold mb-4">Step 1: Basic Information</h2>
        
        <div class="space-y-4">
            {{-- Subject --}}
            <div>
                <label class="block text-sm font-medium mb-1">Subject *</label>
                <input type="text" 
                       wire:model="form.subject" 
                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100" 
                       placeholder="Brief description of your issue">
                @error('form.subject') 
                    <span class="text-red-500 text-xs">{{ $message }}</span> 
                @enderror
            </div>

            {{-- Organization (hidden for clients) --}}
            @if(!$this->isClientUser())
            <div>
                <label class="block text-sm font-medium mb-1">Organization *</label>
                <select wire:model.live="form.organization_id" 
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    <option value="">Select Organization</option>
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
                @error('form.organization_id') 
                    <span class="text-red-500 text-xs">{{ $message }}</span> 
                @enderror
            </div>
            @endif

            {{-- User (for admins/support only) --}}
            @if(!$this->isClientUser())
            <div>
                <label class="block text-sm font-medium mb-1">User *</label>
                <select wire:model="form.client_id" 
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100"
                        {{ empty($form['organization_id']) ? 'disabled' : '' }}>
                    <option value="">{{ empty($form['organization_id']) ? 'Select Organization first' : 'Select User' }}</option>
                    @foreach($this->availableClients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                    {{-- Debug info --}}
                    @if($this->availableClients->isEmpty())
                        <option disabled>No users found for this organization</option>
                    @endif
                </select>
                @error('form.client_id') 
                    <span class="text-red-500 text-xs">{{ $message }}</span> 
                @enderror
            </div>
            @endif

            {{-- Department --}}
            <div>
                <label class="block text-sm font-medium mb-1">Department *</label>
                <select wire:model="form.department_id" 
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    <option value="">Select Department</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
                @error('form.department_id') 
                    <span class="text-red-500 text-xs">{{ $message }}</span> 
                @enderror
            </div>

            {{-- Priority --}}
            <div>
                <label class="block text-sm font-medium mb-1">Priority</label>
                <select wire:model="form.priority" 
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    @foreach($priorityOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('form.priority') 
                    <span class="text-red-500 text-xs">{{ $message }}</span> 
                @enderror
            </div>
        </div>

        {{-- Next Step Button --}}
        <div class="mt-6 flex justify-end">
            <button wire:click="nextStep" 
                    class="px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-md">
                Next Step
            </button>
        </div>
    </div>
    @endif

    {{-- Step 2: Hardware Selection (only for hardware departments) --}}
    @if($currentStep === 2 && $this->isHardwareDepartment())
    {{-- Debug info --}}
    <!-- Current step: {{ $currentStep }}, Is hardware: {{ $this->isHardwareDepartment() ? 'true' : 'false' }} -->
    <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 shadow-md">
        <h2 class="text-lg font-semibold mb-4">Step 2: Hardware Selection</h2>
        
        <div class="mb-4">
            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                Select the hardware items related to this ticket. This helps us better understand and resolve your issue.
            </p>
        </div>

        @if($this->availableHardware->isEmpty())
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <div class="flex items-center">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-500 mr-3" />
                    <div>
                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                            No hardware found
                        </p>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            No hardware items are registered for this organization. You can continue without selecting hardware.
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="space-y-3">
                @foreach($this->availableHardware as $hardware)
                <div class="border border-neutral-200 dark:border-neutral-700 rounded-lg overflow-hidden">
                    <div class="flex items-center p-4 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                        <input type="checkbox" 
                               wire:model="form.selected_hardware" 
                               value="{{ $hardware->id }}"
                               id="hardware-{{ $hardware->id }}"
                               class="mr-3 rounded border-neutral-300 text-sky-600 focus:ring-sky-500">
                        
                        <label for="hardware-{{ $hardware->id }}" class="flex-1 cursor-pointer">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-neutral-900 dark:text-neutral-100">
                                        {{ $hardware->brand }} {{ $hardware->model }}
                                    </p>
                                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                        @if($hardware->type)
                                            {{ $hardware->type->name }} • 
                                        @endif
                                        Quantity: {{ $hardware->quantity }}
                                    </p>
                                    @if($hardware->location)
                                        <p class="text-xs text-neutral-500 dark:text-neutral-500">
                                            Location: {{ $hardware->location }}
                                        </p>
                                    @endif
                                    @if($hardware->serial_number)
                                        <p class="text-xs text-neutral-500 dark:text-neutral-500">
                                            Serial: {{ $hardware->serial_number }}
                                        </p>
                                    @endif
                                    @if($hardware->asset_tag)
                                        <p class="text-xs text-neutral-500 dark:text-neutral-500">
                                            Asset: {{ $hardware->asset_tag }}
                                        </p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($hardware->serials->isNotEmpty())
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200">
                                            {{ $hardware->serials->count() }} Serial{{ $hardware->serials->count() > 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                    @if($hardware->contract)
                                        <span class="text-xs bg-sky-100 dark:bg-sky-900/30 text-sky-800 dark:text-sky-200 px-2 py-1 rounded">
                                            {{ $hardware->contract->contract_number }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </label>
                    </div>
                    
                    {{-- Serial Selection (if hardware is selected and has serials) --}}
                    @if(in_array($hardware->id, $form['selected_hardware']) && $hardware->serials->isNotEmpty())
                        <div class="border-t border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-700/30 p-4">
                            <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Select Serial Numbers (Optional):
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($hardware->serials as $serial)
                                    <label class="flex items-center p-2 bg-white dark:bg-neutral-800 rounded border border-neutral-200 dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 cursor-pointer">
                                        <input type="checkbox" 
                                               wire:model="form.hardware_serials.{{ $hardware->id }}" 
                                               value="{{ $serial->id }}"
                                               class="mr-2 rounded border-neutral-300 text-sky-600 focus:ring-sky-500">
                                        <div>
                                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                                {{ $serial->serial }}
                                            </p>
                                            @if($serial->notes)
                                                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                                    {{ $serial->notes }}
                                                </p>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
        @endif

        {{-- Navigation Buttons --}}
        <div class="mt-6 flex justify-between">
            <button wire:click="previousStep" 
                    class="px-6 py-2 bg-neutral-200 dark:bg-neutral-700 text-neutral-800 dark:text-neutral-100 rounded-md">
                Previous Step
            </button>

            <button wire:click="nextStepFromHardware" 
                    class="px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-md">
                Next Step
            </button>
        </div>
    </div>
    @endif

    {{-- Step 3: Issue Details (or Step 2 for non-hardware departments) --}}
    @if($currentStep === 3 || ($currentStep === 2 && !$this->isHardwareDepartment()))
    <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 shadow-md">
        <h2 class="text-lg font-semibold mb-4">
            @if($this->isHardwareDepartment())
                Step 3: Issue Details
            @else
                Step 2: Issue Details
            @endif
        </h2>
        
        <div class="space-y-4">
            {{-- Description --}}
            <div>
                <label class="block text-sm font-medium mb-1">Description *</label>
                <textarea wire:model="form.description" 
                          rows="6"
                          class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100"
                          placeholder="Please provide detailed information about your issue..."></textarea>
                @error('form.description') 
                    <span class="text-red-500 text-xs">{{ $message }}</span> 
                @enderror
            </div>

            {{-- File Attachments --}}
            <div>
                <label class="block text-sm font-medium mb-1">Attachments (Optional)</label>
                <div class="border-2 border-dashed border-neutral-300 dark:border-neutral-600 rounded-lg p-6 text-center transition-colors"
                     x-data="{ dragOver: false }"
                     x-on:dragover.prevent="dragOver = true"
                     x-on:dragleave.prevent="dragOver = false"
                     x-on:drop.prevent="dragOver = false; $wire.upload('attachments', $event.dataTransfer.files)">
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-center">
                            <x-heroicon-o-cloud-arrow-up class="w-8 h-8 text-neutral-400" />
                        </div>
                        
                        <div>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                Drag and drop files here, or 
                                <label for="file-upload" class="text-sky-600 hover:text-sky-500 cursor-pointer">
                                    browse files
                                </label>
                            </p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-500 mt-1">
                                Maximum 10MB per file. Supported: Images, PDFs, Documents
                            </p>
                        </div>
                        
                        <input id="file-upload" 
                               type="file" 
                               wire:model="attachments" 
                               multiple
                               class="hidden"
                               accept="image/*,.pdf,.doc,.docx,.txt,.xls,.xlsx">
                    </div>
                </div>

                {{-- File List --}}
                @if(!empty($attachments))
                <div class="mt-4 space-y-2">
                    <h4 class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Selected Files:</h4>
                    @foreach($attachments as $index => $attachment)
                    <div class="flex items-center justify-between bg-neutral-50 dark:bg-neutral-700 rounded-lg p-3">
                        <div class="flex items-center space-x-3">
                            <x-heroicon-o-document class="w-5 h-5 text-neutral-400" />
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                    {{ $attachment->getClientOriginalName() }}
                                </p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                    {{ number_format($attachment->getSize() / 1024, 1) }} KB
                                </p>
                            </div>
                        </div>
                        <button type="button" 
                                wire:click="removeAttachment({{ $index }})"
                                class="text-red-500 hover:text-red-700 dark:hover:text-red-400">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </button>
                    </div>
                    @endforeach
                </div>
                @endif

                @error('attachments.*') 
                    <span class="text-red-500 text-xs mt-2 block">{{ $message }}</span> 
                @enderror
            </div>

            {{-- Summary --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <h3 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-3">Ticket Summary</h3>
                <div class="space-y-2 text-sm">
                    <div>
                        <span class="text-blue-700 dark:text-blue-300 font-medium">Subject:</span>
                        <span class="text-blue-800 dark:text-blue-200 ml-2">{{ $form['subject'] ?: 'Not specified' }}</span>
                    </div>
                    <div>
                        <span class="text-blue-700 dark:text-blue-300 font-medium">Department:</span>
                        <span class="text-blue-800 dark:text-blue-200 ml-2">
                            @if($form['department_id'])
                                @php $dept = $departments->firstWhere('id', $form['department_id']) @endphp
                                {{ $dept ? $dept->name : 'Not specified' }}
                            @else
                                Not specified
                            @endif
                        </span>
                    </div>
                    <div>
                        <span class="text-blue-700 dark:text-blue-300 font-medium">Priority:</span>
                        <span class="text-blue-800 dark:text-blue-200 ml-2">
                            @if($form['priority'])
                                {{ $priorityOptions[$form['priority']] ?? $form['priority'] }}
                            @else
                                Not specified
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Navigation Buttons --}}
        <div class="mt-6 flex justify-between">
            <button wire:click="previousStep" 
                    class="px-6 py-2 bg-neutral-200 dark:bg-neutral-700 text-neutral-800 dark:text-neutral-100 rounded-md">
                Previous Step
            </button>

            <button wire:click="submit" 
                    class="px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-md">
                Create Ticket
            </button>
        </div>
    </div>
    @endif

    {{-- Critical Priority Confirmation Modal --}}
    @if($showCriticalConfirmation)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex items-center mb-4">
                <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-500 mr-3" />
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                    High Priority Ticket
                </h3>
            </div>
            
            <div class="mb-4">
                <p class="text-neutral-700 dark:text-neutral-300 mb-3">
                    You're creating a <strong class="text-red-600">{{ ucfirst($form['priority']) }}</strong> priority ticket. 
                    For urgent issues, please consider calling the hotline for immediate assistance.
                </p>
                
                <div class="flex items-start">
                    <input type="checkbox" 
                           wire:model.live="criticalConfirmed" 
                           id="critical-confirmed"
                           class="mt-1 mr-3 rounded border-neutral-300 text-sky-600 focus:ring-sky-500">
                    <label for="critical-confirmed" class="text-sm text-neutral-700 dark:text-neutral-300">
                        I understand this is a high priority ticket and will call the hotline if needed
                    </label>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" 
                        wire:click="cancelCriticalConfirmation"
                        class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-neutral-100 dark:bg-neutral-700 rounded-md hover:bg-neutral-200 dark:hover:bg-neutral-600 transition-colors">
                    Cancel
                </button>
                <button type="button" 
                        wire:click="confirmCriticalAndContinue"
                        @if(!$criticalConfirmed) disabled @endif
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    Continue
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
