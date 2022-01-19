<?php

return [
    // Nome conforme cadastro da aplicação na API de Autorização
    'app_name' => env('APP_NAME'),

    // Chave de cadastro da aplicação na API de Autorização.
    'app_key' => env('APP_KEY'),

    // Chaves usadas no Cache para guardar dados do token
    'cache' => [
        'token_tipo' => 'token_tipo',
        'token_validade' => 'token_validade',
        'token' => 'token',
    ],

    /*
     * Configurações de requisição para API de Autorização.
     */
    'base_url' => env('AUTORIZACAO_URL', 'http://localhost:8000'),
    'connect_timeout' => 10,
    'timeout' => 30,
];
