<div class="space-y-6">
    {{-- Conversation Thread --}}
    <div id="messages-start" class="bg-white/60 dark:bg-neutral-900/50 backdrop-blur-sm rounded-lg border border-neutral-200/50 dark:border-neutral-700/50">
        <div class="px-6 py-4 border-b border-neutral-200/50 dark:border-neutral-700/50">
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">Conversation</h3>
        </div>
        
        <div class="px-6 py-4 space-y-4 max-h-[500px] overflow-y-auto">
            @forelse($ticket->conversation ?? [] as $item)
                <div class="@if(!($item->is_system_message ?? false)) border-b border-neutral-200 dark:border-neutral-700 pb-4 last:border-b-0 last:pb-0 @endif">
                    <x-tickets.conversation-item :item="$item" :ticket="$ticket" />
                </div>
            @empty
                <div class="text-center py-12">
                    <x-heroicon-o-chat-bubble-left-right class="mx-auto h-12 w-12 text-neutral-400" />
                    <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-300">No messages yet</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Start the conversation by sending a reply.</p>
                </div>
            @endforelse
        </div>
    </div>


    {{-- Reply Modal --}}
    @if($showReplyModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showReplyModal') }" x-show="show" x-cloak>
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-show="show" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 wire:click="closeReplyModal"></div>

            <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
                 x-show="show" x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <form wire:submit="sendMessage">
                    <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                Add Reply
                            </h3>
                            <button type="button" wire:click="closeReplyModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                                <x-heroicon-o-x-mark class="h-6 w-6" />
                            </button>
                        </div>

                        <div class="space-y-4">
                            {{-- Reply Message --}}
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Reply Message *</label>
                                <textarea wire:model="replyMessage" 
                                          rows="6" 
                                          class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                          placeholder="Type your reply here..." required></textarea>
                                @error('replyMessage') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Status Selector --}}
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Status after reply</label>
                                <select wire:model="replyStatus" class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                    <option value="in_progress">In Progress</option>
                                    <option value="open">Open</option>
                                    <option value="solution_provided">Solution Provided</option>
                                </select>
                            </div>

                            {{-- File Upload --}}
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Attachments</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-neutral-300 dark:border-neutral-600 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <x-heroicon-o-cloud-arrow-up class="mx-auto h-12 w-12 text-neutral-400" />
                                        <div class="flex text-sm text-neutral-600 dark:text-neutral-400">
                                            <label for="reply-file-upload" class="relative cursor-pointer bg-white dark:bg-neutral-800 rounded-md font-medium text-sky-600 dark:text-sky-400 hover:text-sky-500 dark:hover:text-sky-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-sky-500">
                                                <span>Upload files</span>
                                                <input id="reply-file-upload" wire:model="attachments" type="file" multiple class="sr-only">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">PNG, JPG, PDF up to 10MB each</p>
                                    </div>
                                </div>

                                @if(!empty($attachments))
                                    <div class="mt-3 space-y-2">
                                        @foreach($attachments as $index => $attachment)
                                            <div class="flex items-center justify-between p-2 bg-neutral-100 dark:bg-neutral-700 rounded text-sm">
                                                <span>{{ $attachment->getClientOriginalName() }}</span>
                                                <button type="button" wire:click="removeAttachment({{ $index }})" class="text-red-500 hover:text-red-700">
                                                    <x-heroicon-o-x-mark class="h-4 w-4" />
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:ml-3 sm:w-auto sm:text-sm">
                            <x-heroicon-o-paper-airplane class="h-4 w-4 mr-2" />
                            Send Reply
                        </button>
                        <button type="button" wire:click="closeReplyModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Note Modal --}}
    @if($showNoteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showNoteModal') }" x-show="show" x-cloak>
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-show="show" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 wire:click="closeNoteModal"></div>

            <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                 x-show="show" x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <form wire:submit="addNote">
                    <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                {{ $editingNoteId ? 'Edit Note' : 'Add Note' }}
                            </h3>
                            <button type="button" wire:click="closeNoteModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                                <x-heroicon-o-x-mark class="h-6 w-6" />
                            </button>
                        </div>

                        <div class="space-y-4">
                            {{-- Note Content --}}
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Note Content *</label>
                                <textarea wire:model="note" 
                                          rows="4" 
                                          class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                          placeholder="Add your note here..." required></textarea>
                                @error('note') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Note Color (Radio Buttons) --}}
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Color</label>
                                <div class="flex items-center gap-4">
                                    <label class="flex items-center">
                                        <input type="radio" wire:model="noteColor" value="sky" class="text-sky-600 focus:ring-sky-500">
                                        <span class="ml-2 w-4 h-4 bg-sky-500 rounded-full"></span>
                                        <span class="ml-1 text-sm text-neutral-700 dark:text-neutral-300">Blue</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" wire:model="noteColor" value="green" class="text-green-600 focus:ring-green-500">
                                        <span class="ml-2 w-4 h-4 bg-green-500 rounded-full"></span>
                                        <span class="ml-1 text-sm text-neutral-700 dark:text-neutral-300">Green</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" wire:model="noteColor" value="yellow" class="text-yellow-600 focus:ring-yellow-500">
                                        <span class="ml-2 w-4 h-4 bg-yellow-500 rounded-full"></span>
                                        <span class="ml-1 text-sm text-neutral-700 dark:text-neutral-300">Yellow</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" wire:model="noteColor" value="red" class="text-red-600 focus:ring-red-500">
                                        <span class="ml-2 w-4 h-4 bg-red-500 rounded-full"></span>
                                        <span class="ml-1 text-sm text-neutral-700 dark:text-neutral-300">Red</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" wire:model="noteColor" value="purple" class="text-purple-600 focus:ring-purple-500">
                                        <span class="ml-2 w-4 h-4 bg-purple-500 rounded-full"></span>
                                        <span class="ml-1 text-sm text-neutral-700 dark:text-neutral-300">Purple</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Internal Only Toggle --}}
                            <div class="flex items-center justify-between">
                                <span class="flex flex-grow flex-col">
                                    <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Internal Only</span>
                                    <span class="text-sm text-neutral-500 dark:text-neutral-400">Only visible to support staff</span>
                                </span>
                                <button type="button" wire:click="$toggle('noteInternal')" 
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 {{ $noteInternal ? 'bg-green-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                                    <span class="sr-only">Internal only</span>
                                    <span aria-hidden="true" 
                                          class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $noteInternal ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                            <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                            {{ $editingNoteId ? 'Update' : 'Add' }} Note
                        </button>
                        <button type="button" wire:click="closeNoteModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Auto-scroll script --}}
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('message-sent', () => {
            setTimeout(() => {
                const messagesStart = document.getElementById('messages-start');
                if (messagesStart) {
                    messagesStart.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 100);
        });
    });
</script>