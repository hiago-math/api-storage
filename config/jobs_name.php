<?php

// primeira key refere-se aos dominios presente em app/Domain

return [
    'ler_manga' => [
        'baixa' => env('APP_NAME') . '-' . env('APP_ENV') . '_baixa',
        'media' => env('APP_NAME') . '-' . env('APP_ENV') . '_media',
        'alta' => env('APP_NAME') . '-' . env('APP_ENV') . '_alta',
    ]
];
