<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket Update - {{ $ticket->ticket_number }}</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2>Ticket {{ ucfirst($updateType) }}</h2>
        <p>The status of your ticket has changed.</p>
        <p><strong>Ticket:</strong> {{ $ticket->ticket_number }}</p>
        <p><strong>Status:</strong> {{ ucfirst($ticket->status) }}</p>
        <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p>Reply to this email to continue the conversation.</p>
            <p><strong>Reply Address:</strong> {{ $replyAddress }}</p>
        </div>
    </div>
</body>
</html>
