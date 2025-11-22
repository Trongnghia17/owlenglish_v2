<?php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'public/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:5173', 'https://lead.suacuu.vn'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];