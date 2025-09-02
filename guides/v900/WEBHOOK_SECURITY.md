# Webhook Security Configuration

## Critical Security Fix

The email webhook endpoint (`/webhooks/email`) has been secured to prevent unauthorized access and potential abuse.

## What Was Fixed

- **Before**: Webhook was completely unprotected, bypassing all middleware
- **After**: Webhook is protected by signature verification, rate limiting, timestamp validation, IP allowlisting, and feature flag gating

## Required Environment Variables

Add these to your `.env` file:

```env
# Email Webhook Configuration
EMAIL_WEBHOOK_ENABLED=true
EMAIL_WEBHOOK_SECRET=your-super-secret-webhook-key-here
EMAIL_WEBHOOK_IP_ALLOWLIST=192.168.1.100,10.0.0.50  # Optional: comma-separated IPs
EMAIL_WEBHOOK_TIMESTAMP_TOLERANCE=300  # Optional: seconds (default: 300)

# Email Parser Configuration
EMAIL_PARSER_ENABLED=true
EMAIL_PARSER_MAILBOX=support@example.com
EMAIL_PARSER_REPLY_PREFIX=[TICKET-
EMAIL_PARSER_MAX_ATTACHMENT=10485760  # 10MB in bytes
EMAIL_PARSER_ALLOWED_EXTENSIONS=pdf,doc,docx,xls,xlsx,png,jpg,jpeg,gif

# Email Domain Configuration
EMAIL_DOMAIN=yourdomain.com
```

## Security Features

### 1. Feature Flag Gating

- Webhook route is only registered when `EMAIL_WEBHOOK_ENABLED=true`
- Prevents accidental exposure in production

### 2. HMAC Signature Verification

- Uses SHA256 HMAC with a shared secret from `EMAIL_WEBHOOK_SECRET`
- Prevents request tampering and forgery
- Requires `X-Signature` header (base64-encoded HMAC)

### 3. Timestamp Validation

- Prevents replay attacks
- Configurable tolerance (default: 5 minutes)
- Requires `X-Timestamp` header (Unix timestamp)

### 4. Rate Limiting

- Maximum 60 requests per minute per IP
- Prevents abuse and DoS attacks
- Applied at route level with `throttle:60,1`

### 5. IP Allowlisting (Optional)

- Restrict webhook access to specific IP addresses
- Comma-separated list in `EMAIL_WEBHOOK_IP_ALLOWLIST`
- Returns 403 Forbidden for unauthorized IPs

### 6. Comprehensive Logging

- All security events are logged
- Includes IP addresses and user agents
- Helps with monitoring and debugging

## How to Generate a Secure Webhook Secret

```bash
# Generate a 64-character random string
openssl rand -hex 32

# Or use Laravel's built-in generator
php artisan tinker
echo Str::random(64);
```

## Webhook Request Format

Your email service must send requests with these headers:

```text
X-Signature: {BASE64_ENCODED_HMAC_SHA256_SIGNATURE}
X-Timestamp: {UNIX_TIMESTAMP}
Content-Type: application/json
```

### Signature Generation

```php
$timestamp = time();
$payload = $timestamp . '.' . $requestBody;
$signature = base64_encode(hash_hmac('sha256', $payload, $webhookSecret, true));

// Send headers:
// X-Signature: $signature
// X-Timestamp: $timestamp
```

## Testing the Webhook

### 1. Generate a test signature

```php
$secret = 'your-webhook-secret';
$timestamp = time();
$payload = $timestamp . '.' . json_encode(['test' => 'data']);
$signature = base64_encode(hash_hmac('sha256', $payload, $secret, true));

echo "Timestamp: $timestamp\n";
echo "Signature: $signature\n";
```

### 2. Test with curl

```bash
curl -X POST http://your-domain.com/webhooks/email \
  -H "Content-Type: application/json" \
  -H "X-Signature: $signature" \
  -H "X-Timestamp: $timestamp" \
  -d '{"test": "data"}'
```

## Security Best Practices

1. **Keep the webhook secret secure** - Never commit it to version control
2. **Use HTTPS** - Always use HTTPS in production
3. **Monitor logs** - Watch for failed signature verifications
4. **Rotate secrets** - Change the webhook secret periodically
5. **Limit IP access** - Use IP allowlisting for additional security
6. **Enable feature flag** - Only enable webhooks when needed

## Troubleshooting

### Common Issues

1. **"Webhook not configured"** - Set `EMAIL_WEBHOOK_ENABLED=true` and `EMAIL_WEBHOOK_SECRET`
2. **"Missing required headers"** - Ensure `X-Signature` and `X-Timestamp` are sent
3. **"Request expired"** - Check if timestamp is within the tolerance window
4. **"Invalid signature"** - Verify the signature generation matches the expected format
5. **"Access denied"** - Check if client IP is in the allowlist
6. **"Rate limit exceeded"** - Wait before sending more requests

### Debug Mode

Enable debug logging in your `.env`:

```env
LOG_LEVEL=debug
```

## Migration from Unprotected Webhook

If you were previously using the unprotected webhook:

1. **Immediate**: Set `EMAIL_WEBHOOK_ENABLED=true` and configure `EMAIL_WEBHOOK_SECRET`
2. **Update email service**: Include signature headers in webhook requests
3. **Test**: Verify webhook functionality with new security
4. **Monitor**: Watch logs for any issues
5. **Deploy**: The security is now active

## Support

If you encounter issues with the webhook security:

1. Check the Laravel logs (`storage/logs/laravel.log`)
2. Verify your webhook secret is correctly set
3. Ensure your email service is sending the required headers
4. Check if the feature flag is enabled
5. Verify IP allowlist configuration if using
6. Test with the provided examples above
