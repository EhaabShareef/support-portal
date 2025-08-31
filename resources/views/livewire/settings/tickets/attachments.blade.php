<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                    <x-heroicon-o-paper-clip class="h-8 w-8" />
                    Ticket Attachment Settings
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Configure file upload limits, security, and storage options</p>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="resetToDefaults" 
                    wire:confirm="Are you sure you want to reset all attachment settings to their defaults? This cannot be undone."
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
        {{-- File Size & Count Limits --}}
        <div class="space-y-6">
            {{-- Max File Size --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-8 w-8 bg-blue-100 dark:bg-blue-900/40 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-arrow-up-tray class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Maximum File Size</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Set the maximum size for individual file uploads</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="relative flex items-center">
                        <input type="number" wire:model="maxFileSize" min="0.1" max="1000" step="0.1"
                               class="w-20 px-3 py-2 pr-8 border border-neutral-300 dark:border-neutral-600 rounded-l-md bg-white dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200 text-center">
                        <div class="absolute right-0 top-0 bottom-0 flex flex-col border-l border-neutral-300 dark:border-neutral-600">
                            <button type="button" wire:click="$set('maxFileSize', {{ min(1000, $maxFileSize + 0.1) }})"
                                    class="flex-1 px-1.5 py-0.5 bg-neutral-50 dark:bg-neutral-800 hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 transition-colors duration-200 border-b border-neutral-300 dark:border-neutral-600 rounded-tr-md">
                                <x-heroicon-o-chevron-up class="h-3 w-3" />
                            </button>
                            <button type="button" wire:click="$set('maxFileSize', {{ max(0.1, $maxFileSize - 0.1) }})"
                                    class="flex-1 px-1.5 py-0.5 bg-neutral-50 dark:bg-neutral-800 hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 transition-colors duration-200 rounded-br-md">
                                <x-heroicon-o-chevron-down class="h-3 w-3" />
                            </button>
                        </div>
                    </div>
                    <select wire:model="maxFileSizeUnit" 
                            class="px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-r-md bg-white dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
                        @foreach($fileSizeUnits as $unit)
                            <option value="{{ $unit }}">{{ $unit }}</option>
                        @endforeach
                    </select>
                </div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">
                    Maximum size: {{ number_format($maxFileSize, 1) }} {{ $maxFileSizeUnit }} ({{ number_format($this->getMaxFileSizeInBytes() / 1024 / 1024, 1) }} MB)
                </p>
                @error('maxFileSize')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- File Count Limits --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-8 w-8 bg-green-100 dark:bg-green-900/40 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-document class="h-4 w-4 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">File Count Limits</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Set maximum files per ticket and message</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Files per Ticket</label>
                        <div class="flex items-center gap-2">
                            <button wire:click="$set('maxFilesPerTicket', {{ max(1, $maxFilesPerTicket - 1) }})"
                                    class="p-1 rounded-md bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-600 dark:text-neutral-400 transition-colors duration-200">
                                <x-heroicon-o-minus class="h-4 w-4" />
                            </button>
                            <span class="w-8 text-center text-sm font-medium text-neutral-800 dark:text-neutral-200">{{ $maxFilesPerTicket }}</span>
                            <button wire:click="$set('maxFilesPerTicket', {{ min(50, $maxFilesPerTicket + 1) }})"
                                    class="p-1 rounded-md bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-600 dark:text-neutral-400 transition-colors duration-200">
                                <x-heroicon-o-plus class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Files per Message</label>
                        <div class="flex items-center gap-2">
                            <button wire:click="$set('maxFilesPerMessage', {{ max(1, $maxFilesPerMessage - 1) }})"
                                    class="p-1 rounded-md bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-600 dark:text-neutral-400 transition-colors duration-200">
                                <x-heroicon-o-minus class="h-4 w-4" />
                            </button>
                            <span class="w-8 text-center text-sm font-medium text-neutral-800 dark:text-neutral-200">{{ $maxFilesPerMessage }}</span>
                            <button wire:click="$set('maxFilesPerMessage', {{ min(20, $maxFilesPerMessage + 1) }})"
                                    class="p-1 rounded-md bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-600 dark:text-neutral-400 transition-colors duration-200">
                                <x-heroicon-o-plus class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>
                @error('maxFilesPerTicket')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                @enderror
                @error('maxFilesPerMessage')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Storage Settings --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-8 w-8 bg-purple-100 dark:bg-purple-900/40 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-server class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Storage Settings</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Configure file storage and compression</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Storage Location</label>
                        <select wire:model="storageLocation" 
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
                            @foreach($storageOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Auto-compress Images</label>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Automatically compress uploaded images</p>
                        </div>
                        <button type="button" wire:click="$toggle('autoCompressImages')" 
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 {{ $autoCompressImages ? 'bg-sky-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                            <span class="sr-only">Auto-compress images</span>
                            <span aria-hidden="true" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $autoCompressImages ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>
                    
                    @if($autoCompressImages)
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Image Quality</label>
                            <div class="flex items-center gap-3">
                                <input type="range" wire:model="imageQuality" min="10" max="100" step="5"
                                       class="flex-1 h-2 bg-neutral-200 dark:bg-neutral-700 rounded-lg appearance-none cursor-pointer slider">
                                <span class="text-sm font-medium text-neutral-800 dark:text-neutral-200 w-12">{{ $imageQuality }}%</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- File Types & Security --}}
        <div class="space-y-6">
            {{-- Allowed File Types --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-8 w-8 bg-green-100 dark:bg-green-900/40 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-check-circle class="h-4 w-4 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Allowed File Types</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Select which file types are allowed</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto">
                    @foreach($defaultAllowedTypes as $type => $label)
                        <label class="flex items-center gap-2 p-2 rounded-md hover:bg-neutral-50 dark:hover:bg-neutral-700 cursor-pointer">
                            <input type="checkbox" wire:click="toggleFileType('{{ $type }}', 'allowed')" 
                                   @checked(in_array($type, $allowedFileTypes))
                                   class="h-4 w-4 text-sky-600 border-neutral-300 rounded focus:ring-sky-500">
                            <span class="text-sm text-neutral-700 dark:text-neutral-300">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Blocked File Types --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-8 w-8 bg-red-100 dark:bg-red-900/40 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-x-circle class="h-4 w-4 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Blocked File Types</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Select which file types are blocked</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto">
                    @foreach($defaultBlockedTypes as $type => $label)
                        <label class="flex items-center gap-2 p-2 rounded-md hover:bg-neutral-50 dark:hover:bg-neutral-700 cursor-pointer">
                            <input type="checkbox" wire:click="toggleFileType('{{ $type }}', 'blocked')" 
                                   @checked(in_array($type, $blockedFileTypes))
                                   class="h-4 w-4 text-red-600 border-neutral-300 rounded focus:ring-red-500">
                            <span class="text-sm text-neutral-700 dark:text-neutral-300">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Security Settings --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-8 w-8 bg-orange-100 dark:bg-orange-900/40 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-shield-check class="h-4 w-4 text-orange-600 dark:text-orange-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Security Settings</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Configure file security and scanning</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Scan for Viruses</label>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Enable virus scanning for uploaded files</p>
                        </div>
                        <button type="button" wire:click="$toggle('scanForViruses')" 
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 {{ $scanForViruses ? 'bg-sky-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                            <span class="sr-only">Scan for viruses</span>
                            <span aria-hidden="true" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $scanForViruses ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Require File Scan</label>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Block files until scan completes</p>
                        </div>
                        <button type="button" wire:click="$toggle('requireFileScan')" 
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 {{ $requireFileScan ? 'bg-sky-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                            <span class="sr-only">Require file scan</span>
                            <span aria-hidden="true" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $requireFileScan ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- UI Settings --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-8 w-8 bg-indigo-100 dark:bg-indigo-900/40 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-cog-6-tooth class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">UI Settings</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Configure upload interface options</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Show File Preview</label>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Display file previews before upload</p>
                        </div>
                        <button type="button" wire:click="$toggle('showFilePreview')" 
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 {{ $showFilePreview ? 'bg-sky-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                            <span class="sr-only">Show file preview</span>
                            <span aria-hidden="true" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $showFilePreview ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Enable Drag & Drop</label>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Allow drag and drop file uploads</p>
                        </div>
                        <button type="button" wire:click="$toggle('enableDragDrop')" 
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 {{ $enableDragDrop ? 'bg-sky-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                            <span class="sr-only">Enable drag and drop</span>
                            <span aria-hidden="true" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $enableDragDrop ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Show Upload Progress</label>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Display upload progress indicators</p>
                        </div>
                        <button type="button" wire:click="$toggle('showUploadProgress')" 
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 {{ $showUploadProgress ? 'bg-sky-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                            <span class="sr-only">Show upload progress</span>
                            <span aria-hidden="true" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $showUploadProgress ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
