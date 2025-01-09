<?php

return [
    'host' => env('RABBITMQ_HOST'),
    'port' => env('RABBITMQ_PORT'),
    'user' => env('RABBITMQ_USER'),
    'password' => env('RABBITMQ_PASSWORD'),
    'exchange' => env('RABBITMQ_EXCHANGE'),
    'options' => [
        'connection_timeout' => 10,
        'read_write_timeout' => 60 * 2,
        'heartbeat' => 60,
        'channel_rpc_timeout' => 60 * 2,
    ],
];
