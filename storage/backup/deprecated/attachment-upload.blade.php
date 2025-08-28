<div class="space-y-4">
    {{-- Drag & Drop Zone --}}
    <div class="relative"
         x-data="{ 
             dragOver: false
         }"
         @dragover.prevent="dragOver = true"
         @dragleave.prevent="dragOver = false"
         @drop.prevent="dragOver = false; $refs.fileInput.files = $event.dataTransfer.files; $wire.set('attachments', Array.from($event.dataTransfer.files))">
        
        <div class="border-2 border-dashed rounded-lg p-6 text-center transition-colors"
             :class="dragOver ? 'border-sky-500 bg-sky-50 dark:bg-sky-900/20' : 'border-neutral-300 dark:border-neutral-600'">
            
            <div class="space-y-2">
                <x-heroicon-o-cloud-arrow-up class="mx-auto h-12 w-12 text-neutral-400" />
                
                <div class="text-sm text-neutral-600 dark:text-neutral-400">
                    <label for="file-input" class="relative cursor-pointer rounded-md font-medium text-sky-600 hover:text-sky-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-sky-500 focus-within:ring-offset-2">
                        <span>Upload files</span>
                        <input id="file-input" x-ref="fileInput" type="file" wire:model="attachments" multiple class="sr-only" accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar">
                    </label>
                    <span class="text-neutral-500 dark:text-neutral-400">or drag and drop</span>
                </div>
                
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    Maximum file size: {{ number_format($maxFileSize / 1024, 1) }} MB
                </p>
            </div>
        </div>
    </div>

    {{-- File List --}}
    @if(count($attachments) > 0)
        <div class="space-y-2">
            <div class="flex items-center justify-between text-sm text-neutral-600 dark:text-neutral-400">
                <span>{{ count($attachments) }} file(s) selected</span>
                <span>Total: {{ $this->getFormattedTotalSize() }}</span>
            </div>
            
            <div class="space-y-2">
                @foreach($attachments as $index => $attachment)
                    <div class="flex items-center justify-between p-3 bg-neutral-50 dark:bg-neutral-800 rounded-lg">
                        <div class="flex items-center space-x-3">
                            {{-- File Icon --}}
                            @php
                                $extension = strtolower(pathinfo($attachment->getClientOriginalName(), PATHINFO_EXTENSION));
                                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                                $isPdf = $extension === 'pdf';
                                $isDoc = in_array($extension, ['doc', 'docx']);
                                $isExcel = in_array($extension, ['xls', 'xlsx']);
                                $isArchive = in_array($extension, ['zip', 'rar']);
                            @endphp
                            
                            @if($isImage)
                                <x-heroicon-o-photo class="h-5 w-5 text-sky-500" />
                            @elseif($isPdf)
                                <x-heroicon-o-document-text class="h-5 w-5 text-red-500" />
                            @elseif($isDoc)
                                <x-heroicon-o-document-text class="h-5 w-5 text-blue-500" />
                            @elseif($isExcel)
                                <x-heroicon-o-table-cells class="h-5 w-5 text-green-500" />
                            @elseif($isArchive)
                                <x-heroicon-o-archive-box class="h-5 w-5 text-orange-500" />
                            @else
                                <x-heroicon-o-document class="h-5 w-5 text-neutral-500" />
                            @endif
                            
                            {{-- File Info --}}
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100 truncate">
                                    {{ $attachment->getClientOriginalName() }}
                                </p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                    {{ number_format($attachment->getSize() / 1024, 1) }} KB
                                </p>
                            </div>
                        </div>
                        
                        {{-- Remove Button --}}
                        <button type="button" 
                                wire:click="removeAttachment({{ $index }})"
                                class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                            <x-heroicon-o-x-mark class="h-4 w-4" />
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Error Messages --}}
    @error('attachments.*')
        <div class="text-sm text-red-600 dark:text-red-400">
            {{ $message }}
        </div>
    @enderror
</div>
