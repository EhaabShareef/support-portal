<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use Livewire\Component;

class QuickActions extends Component
{
    public Ticket $ticket;

    /**
     * Initialize the component with the given Ticket.
     *
     * Called when the Livewire component is mounted; stores the provided Ticket
     * instance on the component for subsequent actions.
     *
     * @param Ticket $ticket The Ticket instance this component will operate on.
     */
    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    /**
     * Dispatches a `reply:toggle` event to the ReplyForm component.
     *
     * Triggers the ReplyForm livewire component to toggle its reply UI for the current ticket.
     */
    public function showReply(): void
    {
        $this->dispatch('reply:toggle')->to(ReplyForm::class);
    }

    /**
     * Toggle the ticket note form.
     *
     * Dispatches the Livewire event `note:toggle` to the NoteForm component so that the note form/modal can open or close for this ticket.
     */
    public function showNote(): void
    {
        $this->dispatch('note:toggle')->to(NoteForm::class);
    }

    /**
     * Toggle the ticket close modal.
     *
     * Dispatches a `close:toggle` event targeted at the CloseModal component to prompt display of the close dialog.
     */
    public function showClose(): void
    {
        $this->dispatch('close:toggle')->to(CloseModal::class);
    }

    /**
     * Toggle the ticket reopen modal.
     *
     * Dispatches the `reopen:toggle` event to the ReopenModal component so the UI can open or close
     * the reopen-ticket modal for the current ticket.
     */
    public function showReopen(): void
    {
        $this->dispatch('reopen:toggle')->to(ReopenModal::class);
    }

    /**
     * Render the quick actions Livewire component view.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function render()
    {
        return view('livewire.tickets.quick-actions');
    }
}
