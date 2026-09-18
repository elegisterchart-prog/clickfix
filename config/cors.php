<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'register'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://48d947e6.clickfix-app.pages.dev',
        'https://preview.clickfix-app.pages.dev',
        'https://trend-tones-jason-removal.trycloudflare.com',
        'https://apollo-township-simulations-phenomenon.trycloudflare.com',
        'https://site-intl-giant-mainstream.trycloudflare.com',
        'https://*.clickfix-app.pages.dev',
        'https://*.trycloudflare.com',
    ],
    'allowed_origins_patterns' => [
        '#^https://.*\\.clickfix-app\\.pages\\.dev$#',
        '#^https://.*\\.trycloudflare\\.com$#',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];