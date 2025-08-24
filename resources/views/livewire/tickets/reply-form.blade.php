<div>
    @if($show)
        <div class="bg-white/60 dark:bg-neutral-900/50 backdrop-blur-sm rounded-lg border border-neutral-200/50 dark:border-neutral-700/50 mb-4">
            <div class="px-6 py-4 border-b border-neutral-200/50 dark:border-neutral-700/50">
                <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">Send Reply</h3>
            </div>
            <div class="px-6 py-4">
                <form wire:submit.prevent="sendMessage" class="space-y-4">
                    <div>
                        <label for="replyMessage" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Message</label>
                        <textarea wire:model="replyMessage" 
                                  id="replyMessage"
                                  rows="4"
                                  class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500"
                                  placeholder="Type your reply..."></textarea>
                    </div>
                    
                    <div>
                        <label for="replyStatus" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Status</label>
                        <select wire:model="replyStatus" 
                                id="replyStatus"
                                class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500">
                            <option value="in_progress">In Progress</option>
                            <option value="open">Open</option>
                            <option value="solution_provided">Solution Provided</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="cc" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">CC Recipients</label>
                        <input type="text" 
                               wire:model="cc" 
                               id="cc"
                               placeholder="email1@example.com, email2@example.com" 
                               class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500" />
                    </div>
                    
                    <div>
                        <label for="attachments" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Attachments</label>
                        <input type="file" 
                               wire:model="attachments" 
                               id="attachments"
                               multiple 
                               class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100" />
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button type="button" 
                                wire:click="toggle" 
                                class="btn-secondary"
                                aria-label="Cancel reply">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="btn-primary"
                                wire:loading.attr="disabled"
                                aria-label="Send reply">
                            <span wire:loading.remove>Send Reply</span>
                            <span wire:loading>Sending...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
