<?php

return [
    // Nome conforme cadastro da aplicação na API de Autorização
    'app_name' => env('APP_NAME'),

    // Chave de cadastro da aplicação na API de Autorização.
    'app_key' => env('APP_KEY'),

    /*
     * Configurações de requisição para API de Autorização.
     */
    'base_url' => env('AUTORIZACAO_URL', 'http://localhost:8000'),
    'connect_timeout' => 10,
    'timeout' => 30,

    // Recomendado usar cache com o driver database ou redis caso a aplicação use autobalance.
    'cache' => [
        'ativo' => false, // Salvar token em cache

        // Chaves usadas no Cache para guardar dados do token caso ativo = true
        'token_tipo' => 'token_tipo',
        'token_validade' => 'token_validade',
        'token' => 'token',
    ],
];
