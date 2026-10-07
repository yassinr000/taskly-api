<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    // FRONTEND_URL accepte plusieurs adresses séparées par des virgules.
    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('FRONTEND_URL', 'http://localhost:5173'))
    ))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
