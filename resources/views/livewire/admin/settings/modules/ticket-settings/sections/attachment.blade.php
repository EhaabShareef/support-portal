<x-settings.section title="Attachment Configuration" description="Configure file upload limits and settings">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Max File Size --}}
        <div>
            <label for="attachmentMaxSizeMb" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                Maximum File Size (MB)
            </label>
            <input 
                type="number" 
                wire:model="attachmentMaxSizeMb" 
                id="attachmentMaxSizeMb"
                min="1" 
                max="100"
                class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 focus:border-sky-500 focus:ring-sky-500"
            />
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                Maximum size for individual file uploads
            </p>
        </div>

        {{-- Max File Count --}}
        <div>
            <label for="attachmentMaxCount" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                Maximum File Count
            </label>
            <input 
                type="number" 
                wire:model="attachmentMaxCount" 
                id="attachmentMaxCount"
                min="1" 
                max="20"
                class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 focus:border-sky-500 focus:ring-sky-500"
            />
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                Maximum number of files per ticket
            </p>
        </div>

        {{-- Image Compression --}}
        <div>
            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    wire:model="enableImageCompression" 
                    id="enableImageCompression"
                    class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-neutral-300 rounded"
                />
                <label for="enableImageCompression" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                    Enable Image Compression
                </label>
            </div>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                Automatically compress uploaded images to save storage
            </p>
        </div>

        {{-- Image Compression Quality --}}
        @if($enableImageCompression)
        <div>
            <label for="imageCompressionQuality" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                Image Compression Quality (%)
            </label>
            <input 
                type="number" 
                wire:model="imageCompressionQuality" 
                id="imageCompressionQuality"
                min="10" 
                max="100"
                class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 focus:border-sky-500 focus:ring-sky-500"
            />
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                Quality level for compressed images (higher = better quality, larger file)
            </p>
        </div>
        @endif

        {{-- Virus Scanning --}}
        <div>
            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    wire:model="scanForViruses" 
                    id="scanForViruses"
                    class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-neutral-300 rounded"
                />
                <label for="scanForViruses" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                    Scan Files for Viruses
                </label>
            </div>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                Scan uploaded files for malware (requires antivirus service)
            </p>
        </div>
    </div>

    {{-- Allowed File Types --}}
    <div class="mt-8">
        <h4 class="text-lg font-medium text-neutral-800 dark:text-neutral-100 mb-4">Allowed File Types</h4>
        
        <div class="space-y-4">
            {{-- Add New File Type --}}
            <div class="flex gap-2">
                <input 
                    type="text" 
                    wire:model.defer="newFileType" 
                    placeholder="Enter file extension (e.g., pdf)"
                    class="flex-1 rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 focus:border-sky-500 focus:ring-sky-500"
                />
                <button 
                    wire:click="addFileType($event.target.value); $event.target.value = ''"
                    class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-colors duration-200"
                >
                    Add
                </button>
            </div>

            {{-- File Types List --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                @foreach($allowedFileTypes as $index => $fileType)
                    <div class="flex items-center justify-between p-2 bg-neutral-50 dark:bg-neutral-800 rounded-md">
                        <span class="text-sm text-neutral-700 dark:text-neutral-300">.{{ $fileType }}</span>
                        <button 
                            wire:click="removeFileType({{ $index }})"
                            class="text-red-500 hover:text-red-700 transition-colors duration-200"
                        >
                            <x-heroicon-o-x-mark class="h-4 w-4" />
                        </button>
                    </div>
                @endforeach
            </div>

            @if(empty($allowedFileTypes))
                <p class="text-sm text-neutral-500 dark:text-neutral-400 italic">
                    No file types configured. All file types will be allowed.
                </p>
            @endif
        </div>
    </div>
</x-settings.section>
