<div class="space-y-6">
    {{-- Header with subject, badges, and quick actions --}}
    <x-tickets.header :ticket="$ticket" />
    
    {{-- Main body grid: conversation (left) + details/notes (right) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        {{-- Left column: Conversation thread --}}
        <div class="lg:col-span-8 order-2 lg:order-1">
            <livewire:tickets.conversation-thread :ticket="$ticket" />
        </div>
        
        {{-- Right column: Details, organization notes, internal notes --}}
        <div class="lg:col-span-4 space-y-4 order-1 lg:order-2">
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
    <livewire:tickets.split-ticket-modal :ticket="$ticket" />
    <livewire:tickets.merge-tickets-modal :ticket="$ticket" />
    <livewire:tickets.attachment-preview-modal wire:ref="attachmentModal" />
</div>

<script>
function confirmTicketUpdate(event, currentPriority) {
    console.log('confirmTicketUpdate called', { currentPriority });
    
    const form = event.target;
    const prioritySelect = form.querySelector('#prioritySelect');
    
    if (!prioritySelect) {
        console.error('Priority select not found');
        return true; // Allow submission if we can't find the select
    }
    
    const newPriority = prioritySelect.value;
    console.log('Priority comparison', { currentPriority, newPriority });
    
    // Priority hierarchy for comparison
    const priorityLevels = {
        'low': 1,
        'normal': 2,
        'high': 3,
        'urgent': 4,
        'critical': 5
    };
    
    const isEscalation = priorityLevels[newPriority] > priorityLevels[currentPriority];
    console.log('Is escalation?', isEscalation);
    
    if (isEscalation) {
        // Show confirmation for escalation
        const confirmed = confirm(`Are you sure you want to escalate this ticket's priority to ${newPriority}?\n\nThis action will be logged for audit purposes.`);
        console.log('User confirmed escalation?', confirmed);
        
        if (!confirmed) {
            event.preventDefault();
            return false;
        }
    }
    
    console.log('Form submission allowed');
    return true;
}

function handleSaveClick(currentPriority) {
    console.log('handleSaveClick called', { currentPriority });
    
    // Get the priority select value
    const prioritySelect = document.querySelector('#prioritySelect');
    if (!prioritySelect) {
        console.error('Priority select not found');
        return true;
    }
    
    const newPriority = prioritySelect.value;
    console.log('Priority comparison', { currentPriority, newPriority });
    
    // Priority hierarchy for comparison
    const priorityLevels = {
        'low': 1,
        'normal': 2,
        'high': 3,
        'urgent': 4,
        'critical': 5
    };
    
    const isEscalation = priorityLevels[newPriority] > priorityLevels[currentPriority];
    console.log('Is escalation?', isEscalation);
    
    if (isEscalation) {
        // Show confirmation for escalation
        const confirmed = confirm(`Are you sure you want to escalate this ticket's priority to ${newPriority}?\n\nThis action will be logged for audit purposes.`);
        console.log('User confirmed escalation?', confirmed);
        
        if (!confirmed) {
            return false;
        }
    }
    
    console.log('Save allowed, calling Livewire method');
    return true;
}

// Attachment Preview Modal Functions
function openAttachmentPreview(id, originalName, uuid, mimeType, size) {
    // Update modal content
    document.getElementById('modalTitle').textContent = originalName;
    document.getElementById('fileName').textContent = originalName;
    document.getElementById('fileDetails').textContent = `Size: ${(size / 1024).toFixed(1)} KB | Type: ${mimeType}`;
    document.getElementById('downloadLink').href = `/attachments/download/${uuid}`;
    
    // Determine file type and show appropriate preview
    const extension = originalName.split('.').pop().toLowerCase();
    const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(extension);
    const isPdf = extension === 'pdf';
    const previewContent = document.getElementById('previewContent');
    
    if (isImage) {
        previewContent.innerHTML = `
            <div class="flex justify-center">
                <img src="/attachments/download/${uuid}" 
                     alt="${originalName}"
                     class="max-w-full max-h-80 object-contain rounded-lg shadow-lg"
                     loading="lazy">
            </div>
        `;
    } else if (isPdf) {
        previewContent.innerHTML = `
            <div class="flex justify-center">
                <iframe src="/attachments/download/${uuid}#toolbar=0" 
                        class="w-full h-96 border border-neutral-300 dark:border-neutral-600 rounded-lg"
                        title="${originalName}">
                    <p>Your browser does not support PDF preview. 
                        <a href="/attachments/download/${uuid}" class="text-sky-600 hover:text-sky-500">Download the PDF</a> to view it.
                    </p>
                </iframe>
            </div>
        `;
    } else {
        previewContent.innerHTML = `
            <div class="flex flex-col items-center justify-center py-12">
                <svg class="h-16 w-16 text-neutral-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2">
                    No Preview Available
                </h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-4">
                    This file type cannot be previewed. Please download the file to view its contents.
                </p>
                <a href="/attachments/download/${uuid}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-sky-600 hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition-colors">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download File
                </a>
            </div>
        `;
    }
    
    // Show modal
    document.getElementById('attachmentModal').classList.remove('hidden');
}

function closeAttachmentModal() {
    document.getElementById('attachmentModal').classList.add('hidden');
}

// Close modal on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAttachmentModal();
    }
});
</script>
