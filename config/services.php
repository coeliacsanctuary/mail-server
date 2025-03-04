<?php

declare(strict_types=1);

return [

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'coeliac' => [
        'url' => env('COELIAC_SANCTUARY_URL'),
        'key' => env('COELIAC_KEY'),
    ],
];
