<div>
    @if($show)
        {{-- Modal Backdrop --}}
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('show') }" x-show="show" x-cloak>
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-show="show" 
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     wire:click="toggle"></div>

                {{-- Modal Content --}}
                <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
                     x-show="show" x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    
                    <form wire:submit.prevent="sendMessage">
                        {{-- Modal Header --}}
                        <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                    Send Reply
                                </h3>
                                <button type="button" wire:click="toggle" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                                    <x-heroicon-o-x-mark class="h-6 w-6" />
                                </button>
                            </div>

                            {{-- Ticket Info --}}
                            <div class="mb-4 p-3 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                                <div class="text-sm text-neutral-600 dark:text-neutral-400">
                                    <div class="font-medium">Ticket #{{ $ticket->ticket_number }}</div>
                                    <div class="truncate">{{ $ticket->subject }}</div>
                                </div>
                            </div>

                            {{-- Form Fields --}}
                            <div class="space-y-4">
                                {{-- Message --}}
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                        Message
                                    </label>
                                    <textarea wire:model="replyMessage" 
                                              rows="4"
                                              placeholder="Type your reply..."
                                              class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"></textarea>
                                </div>

                                {{-- Status --}}
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                        Update Status
                                    </label>
                                    <select wire:model="replyStatus" 
                                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                        <option value="in_progress">In Progress</option>
                                        <option value="open">Open</option>
                                        <option value="solution_provided">Solution Provided</option>
                                    </select>
                                </div>

                                {{-- CC Recipients --}}
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                        CC Recipients (Optional)
                                    </label>
                                    <input type="text" 
                                           wire:model="cc" 
                                           placeholder="email1@example.com, email2@example.com" 
                                           class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent" />
                                </div>

                                {{-- File Upload --}}
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                        Attachments
                                    </label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-neutral-300 dark:border-neutral-600 border-dashed rounded-md"
                                         x-data="{ 
                                             dragOver: false
                                         }"
                                         @dragover.prevent="dragOver = true"
                                         @dragleave.prevent="dragOver = false"
                                         @drop.prevent="dragOver = false; $refs.fileInput.files = $event.dataTransfer.files; $wire.set('attachments', Array.from($event.dataTransfer.files))">
                                        <div class="space-y-1 text-center"
                                             :class="dragOver ? 'border-sky-500 bg-sky-50 dark:bg-sky-900/20' : ''">
                                            <x-heroicon-o-cloud-arrow-up class="mx-auto h-12 w-12 text-neutral-400" />
                                            <div class="flex text-sm text-neutral-600 dark:text-neutral-400">
                                                <label for="file-upload" class="relative cursor-pointer bg-white dark:bg-neutral-800 rounded-md font-medium text-sky-600 hover:text-sky-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-sky-500">
                                                    <span>Upload a file</span>
                                                    <input id="file-upload" 
                                                           x-ref="fileInput"
                                                           wire:model="attachments" 
                                                           type="file" 
                                                           class="sr-only" 
                                                           multiple
                                                           accept="image/*,.pdf,.doc,.docx,.txt,.zip,.rar">
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                                PNG, JPG, PDF, DOC up to {{ config('app.max_file_size', 10240) / 1024 }}MB
                                            </p>
                                        </div>
                                    </div>
                                    
                                    {{-- File List --}}
                                    @if($attachments)
                                        <div class="mt-2 space-y-2">
                                            @foreach($attachments as $index => $attachment)
                                                <div class="flex items-center justify-between p-2 bg-neutral-50 dark:bg-neutral-700 rounded">
                                                    <div class="flex items-center space-x-2">
                                                        <x-heroicon-o-document class="h-4 w-4 text-neutral-400" />
                                                        <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ $attachment->getClientOriginalName() }}</span>
                                                    </div>
                                                    <button type="button" 
                                                            wire:click="removeAttachment({{ $index }})"
                                                            class="text-red-500 hover:text-red-700">
                                                        <x-heroicon-o-x-mark class="h-4 w-4" />
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    class="w-full inline-flex items-center justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:ml-3 sm:w-auto sm:text-sm"
                                    wire:loading.attr="disabled"
                                    onclick="console.log('Submit button clicked'); console.log('Attachments count:', {{ count($attachments) }});">
                                <span wire:loading.remove class="inline-flex items-center">
                                    <x-heroicon-o-paper-airplane class="h-4 w-4 mr-2" />
                                    Send Reply
                                </span>
                                <span wire:loading class="inline-flex items-center">
                                    <x-heroicon-o-arrow-path class="h-4 w-4 mr-2 animate-spin" />
                                    Sending...
                                </span>
                            </button>
                            <button type="button" wire:click="toggle"
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
