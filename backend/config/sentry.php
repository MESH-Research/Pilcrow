<?php

declare(strict_types=1);

return [
    'dsn' => env('TELEMETRY_ENABLED', false) ? env('TELEMETRY_DSN') : null,

    'release' => env('TELEMETRY_RELEASE'),

    'environment' => env('TELEMETRY_ENVIRONMENT', env('APP_ENV', 'production')),

    'sample_rate' => (float) env('TELEMETRY_ERROR_SAMPLE_RATE', 1.0),

    'traces_sample_rate' => (float) env('TELEMETRY_TRACES_SAMPLE_RATE', 0.0),

    'profiles_sample_rate' => (float) env('TELEMETRY_PROFILES_SAMPLE_RATE', 0.0),

    'send_default_pii' => false,

    'breadcrumbs' => [
        'logs' => true,
        'cache' => false,
        'livewire' => false,
        'sql_queries' => false,
        'sql_bindings' => false,
        'queue_info' => true,
        'command_info' => true,
        'http_client_requests' => true,
        'notifications' => false,
    ],

    'tracing' => [
        'queue_job_transactions' => env('TELEMETRY_TRACE_QUEUES', false),
        'queue_jobs' => env('TELEMETRY_TRACE_QUEUES', false),
        'sql_queries' => false,
        'sql_bindings' => false,
        'redis_commands' => false,
        'http_client_requests' => false,
        'missing_routes' => false,
        'views' => false,
        'livewire' => false,
        'default_integrations' => true,
    ],

    'before_send' => \App\Telemetry\Scrubber::class . '::beforeSend',

    'before_send_transaction' => \App\Telemetry\Scrubber::class . '::beforeSendTransaction',

    'integrations' => [],
];
