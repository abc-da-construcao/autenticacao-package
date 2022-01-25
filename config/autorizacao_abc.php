<?php

return [
	// Nome da aplicação cadastrada na API de Autorização.
    'app_name' => env('APP_NAME'),

    // Recomendado usar cache com o driver database ou redis caso a aplicação use autobalance.
    'cache' => [
        'ativo' => env('TOKEN_CACHE', false), // Salvar token em cache

        // Chaves usadas no Cache para guardar dados do token caso ativo = true
        'token_tipo' => 'token_tipo',
        'token_validade' => 'token_validade',
        'token' => 'token',
    ],

    'acl_session_error' => 'acl_session_error',

    /*
     * Configurações de requisição para API de Autorização.
     */
    'base_url' => env('API_AUTORIZACAO_URL', 'http://eb-autenticacao.eba-9xmwkvhq.us-east-1.elasticbeanstalk.com'),
    'connect_timeout' => 10,
    'timeout' => 30,
];
