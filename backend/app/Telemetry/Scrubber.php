<?php
declare(strict_types=1);

namespace App\Telemetry;

class Scrubber
{
    /**
     * Keys whose values are removed from any reported payload. Compared
     * case-insensitively against request, header, extra, and tag keys.
     */
    public const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'access_token',
        'refresh_token',
        'api_key',
        'authorization',
        'cookie',
        'set-cookie',
        'x-xsrf-token',
        'xsrf-token',
        'remember_token',
        'email',
        'email_address',
        'phone',
        'submission_content',
        'manuscript',
        'body',
        'content',
    ];

    /**
     * GraphQL operation names whose variables should never be reported. Free-text
     * authored content lives in these mutations and could contain PII or
     * unpublished research.
     */
    public const SENSITIVE_OPERATIONS = [
        'UpdateSubmissionContent',
        'CreateInlineComment',
        'CreateOverallComment',
        'UpdateInlineComment',
        'UpdateOverallComment',
        'SubmitReview',
        'UpdateReview',
    ];

    /**
     * Sentry `before_send` hook. Returns the modified Event (as array shape used
     * by sentry-php >=4) with sensitive fields stripped. Returning null drops
     * the event entirely.
     *
     * @param mixed  $event  Sentry\Event instance (typed loosely so the class
     *                        can be referenced from config before the package is
     *                        installed).
     * @param mixed  $_hint
     * @return mixed
     */
    public static function beforeSend($event, $_hint = null)
    {
        if (! is_object($event) || ! method_exists($event, 'getRequest')) {
            return $event;
        }

        $request = $event->getRequest();
        if (is_array($request)) {
            $event->setRequest(self::scrub($request));
        }

        if (method_exists($event, 'getExtra') && method_exists($event, 'setExtra')) {
            $extra = $event->getExtra();
            if (is_array($extra)) {
                $event->setExtra(self::scrub($extra));
            }
        }

        return $event;
    }

    /**
     * Sentry `before_send_transaction` hook. Mirrors beforeSend() — runs the
     * scrubber on transaction payloads (performance traces).
     *
     * @param mixed  $event  Sentry\Event instance (Transaction).
     * @param mixed  $_hint
     * @return mixed
     */
    public static function beforeSendTransaction($event, $_hint = null)
    {
        return self::beforeSend($event, $_hint);
    }

    /**
     * Recursively walk a payload and redact values whose key matches the
     * sensitive list. Also drops GraphQL variables for known sensitive ops.
     *
     * @param array<mixed, mixed>  $payload
     * @return array<mixed, mixed>
     */
    public static function scrub(array $payload): array
    {
        $sensitive = array_map('strtolower', self::SENSITIVE_KEYS);

        return self::walk($payload, $sensitive);
    }

    /**
     * @param array<mixed, mixed>  $payload
     * @param array<int, string>  $sensitive
     * @return array<mixed, mixed>
     */
    private static function walk(array $payload, array $sensitive): array
    {
        $isGraphql = isset($payload['operationName'])
            && in_array($payload['operationName'], self::SENSITIVE_OPERATIONS, true);

        foreach ($payload as $key => $value) {
            $matchKey = is_string($key) ? strtolower($key) : '';

            if ($isGraphql && $matchKey === 'variables') {
                $payload[$key] = '[Filtered]';
                continue;
            }

            if (in_array($matchKey, $sensitive, true)) {
                $payload[$key] = '[Filtered]';
                continue;
            }

            if (is_array($value)) {
                $payload[$key] = self::walk($value, $sensitive);
            }
        }

        return $payload;
    }
}
