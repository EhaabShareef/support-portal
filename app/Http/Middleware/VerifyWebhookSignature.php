<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get webhook configuration from services config
        $config = config('services.email_webhook');
        
        if (!$config || !$config['secret']) {
            Log::error('Email webhook secret not configured', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'path' => $request->path()
            ]);
            return response()->json(['error' => 'Webhook not configured'], 500);
        }

        // Check IP allowlist if configured
        if (!empty($config['ip_allowlist'])) {
            $clientIp = $request->ip();
            if (!in_array($clientIp, $config['ip_allowlist'])) {
                Log::warning('Email webhook access denied - IP not in allowlist', [
                    'ip' => $clientIp,
                    'allowlist' => $config['ip_allowlist'],
                    'path' => $request->path()
                ]);
                return response()->json(['error' => 'Access denied'], 403);
            }
        }

        // Rate limiting: Max 10 requests per minute per IP
        $ip = $request->ip();
        $rateLimitKey = "webhook_rate_limit:{$ip}";
        $rateLimit = Cache::get($rateLimitKey, 0);
        
        if ($rateLimit >= 10) {
            Log::warning('Webhook rate limit exceeded', [
                'ip' => $ip,
                'user_agent' => $request->userAgent(),
                'path' => $request->path()
            ]);
            return response()->json(['error' => 'Rate limit exceeded'], 429);
        }

        // Verify required headers exist
        $signature = $request->header('X-Signature');
        $timestamp = $request->header('X-Timestamp');
        
        if (!$signature || !$timestamp) {
            Log::warning('Webhook missing required headers', [
                'ip' => $ip,
                'user_agent' => $request->userAgent(),
                'path' => $request->path(),
                'has_signature' => !empty($signature),
                'has_timestamp' => !empty($timestamp)
            ]);
            return response()->json(['error' => 'Missing required headers'], 401);
        }

        // Verify timestamp to prevent replay attacks
        $tolerance = $config['timestamp_tolerance'] ?? 300;
        if (abs(time() - (int)$timestamp) > $tolerance) {
            Log::warning('Webhook timestamp expired', [
                'ip' => $ip,
                'timestamp' => $timestamp,
                'current_time' => time(),
                'difference' => abs(time() - (int)$timestamp),
                'tolerance' => $tolerance
            ]);
            return response()->json(['error' => 'Request expired'], 401);
        }

        // Verify HMAC signature
        $payload = $timestamp . '.' . $request->getContent();
        $expected = base64_encode(hash_hmac('sha256', $payload, $config['secret'], true));
        
        if (!hash_equals($expected, $signature)) {
            Log::warning('Webhook signature verification failed', [
                'ip' => $ip,
                'user_agent' => $request->userAgent(),
                'path' => $request->path(),
                'expected' => $expected,
                'received' => $signature
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Increment rate limit counter
        Cache::put($rateLimitKey, $rateLimit + 1, 60);

        // Log successful verification
        Log::info('Webhook signature verified successfully', [
            'ip' => $ip,
            'path' => $request->path(),
            'timestamp' => $timestamp
        ]);

        return $next($request);
    }
}
