<div class="space-y-6">
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <h1 class="text-2xl font-bold text-neutral-800 dark:text-neutral-100">{{ $ticket->subject }}</h1>
    </div>
    <livewire:tickets.quick-actions :ticket="$ticket" />
    <livewire:tickets.conversation-thread :ticket="$ticket" />
    <livewire:tickets.reply-form :ticket="$ticket" />
    <livewire:tickets.note-form :ticket="$ticket" />
    <livewire:tickets.close-modal :ticket="$ticket" />
    <livewire:tickets.reopen-modal :ticket="$ticket" />
</div>
