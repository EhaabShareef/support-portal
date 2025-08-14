<div class="space-y-6">
    {{-- Header with Cancel Option --}}
    <div class="flex justify-between items-center">
        <h1 class="flex text-xl items-center font-semibold text-neutral-800 dark:text-neutral-100">
            <x-heroicon-o-cpu-chip class="inline h-8 w-8 mr-2" />
            Add Hardware – {{ $organization->name }}
        </h1>

        <a href="{{ route('hardware.manage', ['organization' => $organization->id]) }}" 
           class="px-4 py-2 bg-neutral-200 dark:bg-neutral-700 hover:bg-neutral-300 dark:hover:bg-neutral-600 text-sm text-neutral-800 dark:text-neutral-100 rounded-md transition-all duration-200">
            <x-heroicon-o-arrow-left class="inline h-4 w-4 mr-1" /> Cancel & Return
        </a>
    </div>

    {{-- Progress Steps --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg shadow-md p-4">
        <div class="flex items-center justify-between">
            {{-- Step 1: Contract Selection --}}
            <div class="flex items-center {{ $step === 'contract' ? 'text-sky-600 dark:text-sky-400' : ($contractId ? 'text-green-600 dark:text-green-400' : 'text-neutral-400 dark:text-neutral-500') }}">
                <div class="flex items-center justify-center w-8 h-8 rounded-full border-2 
                    {{ $step === 'contract' ? 'border-sky-600 bg-sky-50 dark:border-sky-400 dark:bg-sky-900/20' : ($contractId ? 'border-green-600 bg-green-50 dark:border-green-400 dark:bg-green-900/20' : 'border-neutral-300 dark:border-neutral-600') }}">
                    @if($contractId)
                        <x-heroicon-o-check class="h-4 w-4" />
                    @else
                        <span class="text-sm font-medium">1</span>
                    @endif
                </div>
                <span class="ml-3 font-medium">Select Contract</span>
            </div>

            {{-- Connector Line --}}
            <div class="flex-1 h-px mx-4 {{ $contractId ? 'bg-green-300 dark:bg-green-600' : 'bg-neutral-300 dark:bg-neutral-600' }}"></div>

            {{-- Step 2: Hardware Details --}}
            <div class="flex items-center {{ $step === 'hardware' ? 'text-sky-600 dark:text-sky-400' : ($hardwareId ? 'text-green-600 dark:text-green-400' : 'text-neutral-400 dark:text-neutral-500') }}">
                <div class="flex items-center justify-center w-8 h-8 rounded-full border-2
                    {{ $step === 'hardware' ? 'border-sky-600 bg-sky-50 dark:border-sky-400 dark:bg-sky-900/20' : ($hardwareId ? 'border-green-600 bg-green-50 dark:border-green-400 dark:bg-green-900/20' : 'border-neutral-300 dark:border-neutral-600') }}">
                    @if($hardwareId)
                        <x-heroicon-o-check class="h-4 w-4" />
                    @else
                        <span class="text-sm font-medium">2</span>
                    @endif
                </div>
                <span class="ml-3 font-medium">Hardware Details</span>
            </div>

            {{-- Connector Line --}}
            <div class="flex-1 h-px mx-4 {{ $hardwareId ? 'bg-green-300 dark:bg-green-600' : 'bg-neutral-300 dark:bg-neutral-600' }}"></div>

            {{-- Step 3: Serial Numbers (Conditional) --}}
            <div class="flex items-center {{ $step === 'serials' ? 'text-sky-600 dark:text-sky-400' : ($step === 'done' ? 'text-green-600 dark:text-green-400' : 'text-neutral-400 dark:text-neutral-500') }}">
                <div class="flex items-center justify-center w-8 h-8 rounded-full border-2
                    {{ $step === 'serials' ? 'border-sky-600 bg-sky-50 dark:border-sky-400 dark:bg-sky-900/20' : ($step === 'done' ? 'border-green-600 bg-green-50 dark:border-green-400 dark:bg-green-900/20' : 'border-neutral-300 dark:border-neutral-600') }}">
                    @if($step === 'done')
                        <x-heroicon-o-check class="h-4 w-4" />
                    @else
                        <span class="text-sm font-medium">3</span>
                    @endif
                </div>
                <span class="ml-3 font-medium">{{ $serialRequired ? 'Serial Numbers' : 'Complete' }}</span>
            </div>
        </div>
    </div>

    {{-- Step Content --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg shadow-md">
        @if($step === 'contract')
            <livewire:hardware-contract-selector :organization-id="$organization->id" />
        @elseif($step === 'hardware' && $contractId)
            <livewire:hardware-multi-form :organization-id="$organization->id" :contract-id="$contractId" />
        @elseif($step === 'serials' && !empty($hardwareItems))
            <livewire:hardware-multi-serial-manager :hardware-items="$hardwareItems" />
        @elseif($step === 'done')
            <div class="p-8 text-center">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-green-100 dark:bg-green-900/20 rounded-full">
                    <x-heroicon-o-check-circle class="h-8 w-8 text-green-600 dark:text-green-400" />
                </div>
                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2">Hardware Created Successfully!</h3>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-6">Your hardware has been added to the organization's inventory.</p>
                <div class="flex items-center justify-center gap-3">
                    <a href="{{ route('hardware.manage', ['organization' => $organization->id]) }}" 
                       class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm rounded-md transition-colors duration-200">
                        View Hardware List
                    </a>
                    <a href="{{ route('organizations.hardware.create', ['organization' => $organization->id]) }}" 
                       class="px-4 py-2 bg-neutral-200 dark:bg-neutral-700 hover:bg-neutral-300 dark:hover:bg-neutral-600 text-sm text-neutral-800 dark:text-neutral-100 rounded-md transition-colors duration-200">
                        Add Another
                    </a>
                </div>
            </div>
        @else
            <div class="p-8 text-center">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-neutral-100 dark:bg-neutral-800 rounded-full">
                    <x-heroicon-o-exclamation-triangle class="h-8 w-8 text-neutral-600 dark:text-neutral-400" />
                </div>
                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2">Something went wrong</h3>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-6">Please try again or contact support if the problem persists.</p>
                <a href="{{ route('hardware.manage', ['organization' => $organization->id]) }}" 
                   class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm rounded-md transition-colors duration-200">
                    Return to Hardware List
                </a>
            </div>
        @endif
    </div>
</div>
