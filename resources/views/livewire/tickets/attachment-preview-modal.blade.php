<div>
    @if($show && $attachment)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                 wire:click="closePreview"></div>

            {{-- Modal Content --}}
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    
                    {{-- Modal Header --}}
                    <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                {{ $attachment->original_name }}
                            </h3>
                            <div class="flex items-center space-x-2">
                                {{-- Download Button --}}
                                <button type="button"
                                        wire:click="download"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-sky-600 hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition-colors">
                                    <x-heroicon-o-arrow-down-tray class="h-4 w-4 mr-1" />
                                    Download
                                </button>
                                {{-- Close Button --}}
                                <button type="button" 
                                        wire:click="closePreview"
                                        class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                                    <x-heroicon-o-x-mark class="h-6 w-6" />
                                </button>
                            </div>
                        </div>



                        {{-- File Info --}}
                        <div class="mb-4 p-3 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                            <div class="text-sm text-neutral-600 dark:text-neutral-400">
                                <div class="font-medium">{{ $attachment->original_name }}</div>
                                <div class="text-xs">
                                    Size: {{ number_format($attachment->size / 1024, 1) }} KB | 
                                    Type: {{ $attachment->mime_type ?? 'Unknown' }}
                                </div>
                            </div>
                        </div>

                        {{-- Preview Content --}}
                        <div class="max-h-96 overflow-auto">
                            @if($this->canPreview())
                                @php
                                    $extension = strtolower(pathinfo($attachment->original_name, PATHINFO_EXTENSION));
                                    $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                                    $isPdf = $extension === 'pdf';
                                @endphp

                                @if($isImage)
                                    {{-- Image Preview --}}
                                    <div class="flex justify-center">
                                        <img src="{{ $this->getFileUrl() }}" 
                                             alt="{{ $attachment->original_name }}"
                                             class="max-w-full max-h-80 object-contain rounded-lg shadow-lg"
                                             loading="lazy">
                                    </div>
                                @elseif($isPdf)
                                    {{-- PDF Preview --}}
                                    <div class="flex justify-center">
                                        <iframe src="{{ $this->getFileUrl() }}" 
                                                class="w-full h-96 border border-neutral-300 dark:border-neutral-600 rounded-lg"
                                                title="{{ $attachment->original_name }}">
                                            <p>Your browser does not support PDF preview. 
                                                <button wire:click="download" class="text-sky-600 hover:text-sky-500">Download the PDF</button> to view it.
                                            </p>
                                        </iframe>
                                    </div>
                                @endif
                            @else
                                {{-- No Preview Available --}}
                                <div class="flex flex-col items-center justify-center py-12">
                                    <x-heroicon-o-document class="h-16 w-16 text-neutral-400 mb-4" />
                                    <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2">
                                        No Preview Available
                                    </h3>
                                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-4">
                                        This file type cannot be previewed. Please download the file to view its contents.
                                    </p>
                                    <button wire:click="download"
                                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-sky-600 hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition-colors">
                                        <x-heroicon-o-arrow-down-tray class="h-4 w-4 mr-2" />
                                        Download File
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
