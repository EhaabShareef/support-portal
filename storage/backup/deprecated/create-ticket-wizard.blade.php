<div>
    <div class="mb-4">Step {{ $step }} / 2</div>
    @if($step === 1)
        <div class="space-y-2">
            <input type="text" wire:model="form.subject" placeholder="Subject" class="border p-1 w-full" />
            <input type="text" wire:model="form.organization_id" placeholder="Organization ID" class="border p-1 w-full" />
            <input type="text" wire:model="form.department_id" placeholder="Department ID" class="border p-1 w-full" />
            <input type="text" wire:model="form.owner_id" placeholder="Owner ID" class="border p-1 w-full" />
        </div>
        <div class="mt-4 flex justify-end">
            <button wire:click="next" class="px-4 py-2 bg-gray-200">Next</button>
        </div>
    @else
        <div class="space-y-2">
            <textarea wire:model="form.description" class="border p-1 w-full" rows="5" placeholder="Description"></textarea>
            <input type="file" wire:model="attachments" multiple />
        </div>
        <div class="mt-4 flex justify-between">
            <button wire:click="back" class="px-4 py-2 bg-gray-200">Back</button>
            <button wire:click="save" class="px-4 py-2 bg-gray-200">Finish</button>
        </div>
    @endif
</div>
