<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketNote;
use Illuminate\Http\Request;

class TicketNoteController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'internal_yn' => 'nullable|boolean',
            'color'       => 'nullable|string|max:32',
        ]);

        $validated['ticket_id'] = $ticket->id;
        $validated['internal_yn'] = $request->has('internal_yn');

        TicketNote::create($validated);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Note added.');
    }

    public function destroy(Ticket $ticket, TicketNote $ticketNote)
    {
        $ticketNote->delete();
        return redirect()->route('tickets.show', $ticket)->with('success', 'Note deleted.');
    }
}
