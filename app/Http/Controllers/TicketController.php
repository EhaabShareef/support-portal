<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Organization;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        return view('tickets.index');
    }

    public function create()
    {
        $organizations = Organization::all();
        $departments = Department::all();
        $users = User::all();
        return view('tickets.create', compact('organizations', 'departments', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject'   => 'required|string|max:50',
            'type'      => 'required|in:issue,feedback,bug,lead,task',
            'org_id'    => 'required|exists:organizations,id',
            'client_id' => 'required|exists:users,id',
            'dept_id'   => 'required|exists:departments,id',
            'status'    => 'required|in:in progress,awaiting customer response,awaiting case closure,sales engagement,monitoring,solution provided,closed,on hold',
            'priority'  => 'required|in:Low,Normal,High,Serious Business Impact',
            'owner_id'  => 'required|exists:users,id',
        ]);

        $ticket = Ticket::create($validated);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket created successfully.');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['organization', 'department', 'owner', 'client', 'messages.sender', 'notes.user']);
        return view('tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        $organizations = Organization::all();
        $departments = Department::all();
        $users = User::all();
        return view('tickets.edit', compact('ticket', 'organizations', 'departments', 'users'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'subject'   => 'required|string|max:50',
            'type'      => 'required|in:issue,feedback,bug,lead,task',
            'org_id'    => 'required|exists:organizations,id',
            'client_id' => 'required|exists:users,id',
            'dept_id'   => 'required|exists:departments,id',
            'status'    => 'required|in:in progress,awaiting customer response,awaiting case closure,sales engagement,monitoring,solution provided,closed,on hold',
            'priority'  => 'required|in:Low,Normal,High,Serious Business Impact',
            'owner_id'  => 'required|exists:users,id',
        ]);

        $ticket->update($validated);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket updated successfully.');
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Ticket deleted successfully.');
    }
}
