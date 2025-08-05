<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;

class TicketMessageController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'message'   => 'required|string',
            'sender_id' => 'required|exists:users,id',
        ]);

        $validated['ticket_id'] = $ticket->id;

        TicketMessage::create($validated);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Message added.');
    }

    public function destroy(Ticket $ticket, TicketMessage $ticketMessage)
    {
        $ticketMessage->delete();
        return redirect()->route('tickets.show', $ticket)->with('success', 'Message deleted.');
    }
}
