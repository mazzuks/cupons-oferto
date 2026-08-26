<?php

return [
    'db' => [
        'host' => 'localhost',
        'name' => 'SEU_BANCO',
        'user' => 'SEU_USUARIO',
        'pass' => 'SUA_SENHA',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'base_url' => '',
        'upload_dir' => dirname(__DIR__) . '/uploads/cupons',
        'upload_url' => 'uploads/cupons',
    ],
    'integrations' => [
        'lomadee' => [
            'api_key' => '',
        ],
        'awin' => [
            'access_token' => '',
            'publisher_id' => '',
        ],
        'offer18' => [
            'accounts' => [],
        ],
        'hasoffers' => [
            'accounts' => [],
        ],
    ],
];
