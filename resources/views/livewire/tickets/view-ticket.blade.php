<div class="space-y-6">
    {{-- Header with subject, badges, and quick actions --}}
    <x-tickets.header :ticket="$ticket" />
    
    {{-- Main body grid: conversation (left) + details/notes (right) --}}
    <div class="grid grid-cols-12 gap-4">
        {{-- Left column: Conversation thread --}}
        <div class="col-span-8">
            <livewire:tickets.conversation-thread :ticket="$ticket" />
        </div>
        
        {{-- Right column: Details, organization notes, internal notes --}}
        <div class="col-span-4 space-y-4">
            {{-- Ticket Details --}}
            <x-tickets.details 
                :ticket="$ticket" 
                :editMode="$editMode" 
                :form="$form" 
                :departments="$departments ?? []" 
                :users="$users ?? []" 
                :statusOptions="$statusOptions ?? []" 
                :priorityOptions="$priorityOptions ?? []" 
                :canEdit="$canEdit ?? false"
            />
            
            {{-- Organization Notes --}}
            <x-tickets.organization-note :ticket="$ticket" />
            
            {{-- Internal Notes --}}
            <x-tickets.notes :ticket="$ticket" />
        </div>
    </div>
    
    {{-- Form and Modal Components (hidden by default) --}}
    <livewire:tickets.reply-form :ticket="$ticket" />
    <livewire:tickets.note-form :ticket="$ticket" />
    <livewire:tickets.close-modal :ticket="$ticket" />
    <livewire:tickets.reopen-modal :ticket="$ticket" />
</div>
