<div>
    @if($show)
        <div class="bg-white/60 dark:bg-neutral-900/50 backdrop-blur-sm rounded-lg border border-neutral-200/50 dark:border-neutral-700/50 mb-4">
            <div class="px-6 py-4 border-b border-neutral-200/50 dark:border-neutral-700/50">
                <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">Add Note</h3>
            </div>
            <div class="px-6 py-4">
                <form wire:submit.prevent="addNote" class="space-y-4">
                    <div>
                        <label for="note" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Note</label>
                        <textarea wire:model="note" 
                                  id="note"
                                  rows="3"
                                  class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500"
                                  placeholder="Add your note..."></textarea>
                    </div>
                    
                    <div>
                        <label for="noteColor" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Color</label>
                        <select wire:model="noteColor" 
                                id="noteColor"
                                class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500">
                            <option value="sky">Sky</option>
                            <option value="yellow">Yellow</option>
                            <option value="red">Red</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" 
                                   wire:model="noteInternal" 
                                   id="noteInternal"
                                   class="rounded border-neutral-300 dark:border-neutral-600 text-sky-600 focus:ring-sky-500" />
                            <span class="text-sm text-neutral-700 dark:text-neutral-300">Internal Note</span>
                        </label>
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button type="button" 
                                wire:click="toggle" 
                                class="btn-secondary"
                                aria-label="Cancel note">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="btn-primary"
                                wire:loading.attr="disabled"
                                aria-label="Save note">
                            <span wire:loading.remove>Save Note</span>
                            <span wire:loading>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
