<?php

return [
    'rpc' => [
        'host' => env('MONEROO_HOST', '127.0.0.1'),
        'port' => env('MONEROO_PORT', '38083'),
        'daemon_host' => env('MONEROO_DAEMON_HOST', '127.0.0.1'),
        'daemon_port' => env('MONEROO_DAEMON_PORT', '28081'),
        'main_wallet' => env('MONEROO_MAIN_WALLET', 'marketplace'),
    ],
];
