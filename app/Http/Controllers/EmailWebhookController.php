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
        if (!$this->verifyWebhookSignature($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

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
                'request_data' => $request->all(),
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    private function verifyWebhookSignature(Request $request): bool
    {
        return true;
    }

    private function parseWebhookData(Request $request): array
    {
        return [
            'message_id' => $request->input('message_id'),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'subject' => $request->input('subject'),
            'body' => $request->input('body'),
            'in_reply_to' => $request->input('in_reply_to'),
            'references' => $request->input('references'),
            'headers' => $request->input('headers'),
            'attachments' => $request->input('attachments', []),
        ];
    }
}
