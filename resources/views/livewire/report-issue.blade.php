<div>
    <div x-data="{open:@entangle('show')}">
        <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white dark:bg-neutral-800 p-4 rounded shadow w-80" @click.away="open=false">
                <h2 class="font-semibold mb-2">Report an Issue</h2>
                <textarea wire:model="message" class="w-full h-24 rounded"></textarea>
                <div class="mt-2 flex justify-end space-x-2">
                    <button @click="open=false" class="px-3 py-1 border rounded">Cancel</button>
                    <button wire:click="submit" class="px-3 py-1 border rounded bg-sky-600 text-white">Submit</button>
                </div>
            </div>
        </div>
    </div>
    <div x-data="{show:false}" x-on:issue-reported.window="show=true;setTimeout(()=>show=false,3000)">
        <div x-show="show" class="fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded">Issue submitted</div>
    </div>
</div>
