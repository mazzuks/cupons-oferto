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
    'adsense' => [
        'client_id' => 'ca-pub-1725208559538025',
        'slots' => [
            'v2_topo_responsivo' => '',
            'v2_entre_destaques_e_lista' => '',
            'v2_lateral_300x250' => '',
            'v2_antes_dicas' => '',
            'blog_topo_responsivo' => '',
            'guias_artigo_topo_responsivo' => '',
            'guias_lateral_300x250' => '',
            'sorteios_topo_responsivo' => '',
        ],
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
