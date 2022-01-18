<?php

return [
    // ID de cadastro da aplicação na API de Autorização.
    'app_id' => 1,

    // Chaves usadas no Cache para guardar dados do token
    'cache' => [
        'token_tipo' => 'token_tipo',
        'token_validade' => 'token_validade',
        'token' => 'token',
    ],

    /*
     * Configurações de requisição para API de Autorização.
     */
    'base_url' => 'http://localhost:8000',
    'connect_timeout' => 10,
    'timeout' => 30,
];
