<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket Update - {{ $ticket->ticket_number }}</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2>Ticket Update: {{ $ticket->subject }}</h2>

        <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Ticket Number:</strong> {{ $ticket->ticket_number }}</p>
            <p><strong>Status:</strong> {{ ucfirst($ticket->status) }}</p>
            <p><strong>Priority:</strong> {{ ucfirst($ticket->priority) }}</p>
        </div>

        <div style="margin: 20px 0;">
            <h3>New Message:</h3>
            <div style="background: #fff; padding: 15px; border-left: 4px solid #007cba;">
                {!! nl2br(e($message->message)) !!}
            </div>
        </div>

        <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3>Reply to this ticket:</h3>
            <p>Simply reply to this email to add a message to the ticket. Your reply will be automatically added to the conversation.</p>
            <p><strong>Reply Address:</strong> {{ $replyAddress }}</p>
        </div>

        <div style="text-align: center; margin: 30px 0; color: #666;">
            <p>This is an automated message from your support system.</p>
            <p>Do not reply to this email address directly.</p>
        </div>
    </div>
</body>
</html>
