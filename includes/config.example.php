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
            'v2_topo_responsivo' => '5284508420',
            'v2_entre_destaques_e_lista' => '3971426759',
            'v2_lateral_300x250' => '9321167801',
            'v2_antes_dicas' => '3971426759',
            'blog_topo_responsivo' => '4796538786',
            'guias_artigo_topo_responsivo' => '5834288282',
            'guias_lateral_300x250' => '9112072798',
            'sorteios_topo_responsivo' => '3358394686',
            'oferta_topo_responsivo' => '3971426759',
            'oferta_meio_responsivo' => '3971426759',
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
