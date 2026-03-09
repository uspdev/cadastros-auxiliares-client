<?php

return [
    'enabled' => env('CADASTROS_AUXILIARES_MENSAGENS_INTEGRACAO', false),

    'mensagens' => [
        'endpoint_url' => env('CADASTROS_AUXILIARES_MENSAGENS_ENDPOINT_URL', ''),
        'password' => env('CADASTROS_AUXILIARES_PASSWORD', ''),
        'sistema' => env('CADASTROS_AUXILIARES_SISTEMA_NAME', ''),
        'limite' => (int) env('CADASTROS_AUXILIARES_MENSAGENS_LIMITE', 5),
        'timeout' => (int) env('CADASTROS_AUXILIARES_MENSAGENS_REQUEST_TIMEOUT', 5),
        'retry_times' => (int) env('CADASTROS_AUXILIARES_MENSAGENS_RETRY_TIMES', 1),
        'retry_sleep_ms' => (int) env('CADASTROS_AUXILIARES_MENSAGENS_RETRY_SLEEP_MS', 150),

        'cache' => [
            'enabled' => env('CADASTROS_AUXILIARES_MENSAGENS_CACHE_ENABLED', true),
            'ttl_seconds' => (int) env('CADASTROS_AUXILIARES_MENSAGENS_CACHE_TTL_SECONDS', 30),
            'prefix' => env('CADASTROS_AUXILIARES_MENSAGENS_CACHE_PREFIX', 'cadastros-auxiliares-client:mensagens'),
        ],
    ],
];
