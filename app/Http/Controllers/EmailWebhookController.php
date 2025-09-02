<?php

namespace App\Http\Controllers;

use App\Services\EmailParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailWebhookController extends Controller
{
    public function __construct(private EmailParserService $emailParser)
    {
    }

    public function handleIncomingEmail(Request $request)
    {
        try {
            $emailData = $this->parseWebhookData($request);
            $success = $this->emailParser->parseIncomingEmail($emailData);

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Email processed successfully' : 'Failed to process email'
            ]);
        } catch (\Exception $e) {
            Log::error('Email webhook error', [
                'error' => $e->getMessage(),
                'safe_context' => [
                    'message_id' => $request->input('message_id'),
                    'from'       => $request->input('from'),
                    'to'         => $request->input('to'),
                ],
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    private function parseWebhookData(Request $request): array
    {
        // Normalize headers into array if sent as JSON string
        $headers = $request->input('headers');
        if (is_string($headers)) {
            $decoded = json_decode($headers, true);
            $headers = is_array($decoded) ? $decoded : [];
        }

        // Normalize attachments into array if sent as JSON string
        $attachments = $request->input('attachments', []);
        if (is_string($attachments)) {
            $decoded = json_decode($attachments, true);
            $attachments = is_array($decoded) ? $decoded : [];
        }

        // Normalize email addresses
        $from = $this->normalizeEmailAddress($request->input('from'));
        $to = $this->normalizeEmailAddresses($request->input('to'));
        $replyTo = $this->normalizeEmailAddress($request->input('reply_to'));

        return [
            'message_id'  => $request->input('message_id'),
            'from'        => $from,
            'to'          => $to,
            'reply_to'    => $replyTo,
            'subject'     => $request->input('subject'),
            'body'        => $request->input('body'),
            'in_reply_to' => $request->input('in_reply_to'),
            'references'  => $request->input('references'),
            'headers'     => $headers,
            'attachments' => $attachments,
        ];
    }

    /**
     * Normalize a single email address
     * 
     * @param string|null $email
     * @return string|null
     */
    private function normalizeEmailAddress(?string $email): ?string
    {
        if (empty($email)) {
            return null;
        }

        // Extract email from "Name <email@domain>" format
        if (preg_match('/<(.+?)>/', $email, $matches)) {
            $email = $matches[1];
        }

        // Trim whitespace and quotes
        $email = trim($email, " \t\n\r\0\x0B\"'");

        // Validate email format
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /**
     * Normalize multiple email addresses (handles arrays, comma-separated strings, or single addresses)
     * 
     * @param mixed $emails
     * @return array
     */
    private function normalizeEmailAddresses($emails): array
    {
        if (empty($emails)) {
            return [];
        }

        // If it's already an array, use it directly
        if (is_array($emails)) {
            $emailList = $emails;
        } else {
            // Split by comma if it's a string
            $emailList = array_map('trim', explode(',', (string) $emails));
        }

        // Normalize each email address and filter out invalid ones
        $normalizedEmails = [];
        foreach ($emailList as $email) {
            $normalized = $this->normalizeEmailAddress($email);
            if ($normalized !== null) {
                $normalizedEmails[] = $normalized;
            }
        }

        return $normalizedEmails;
    }
}
