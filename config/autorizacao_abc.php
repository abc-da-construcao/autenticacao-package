<?php

return [
	// Chave de cadastro da aplicação na API de Autorização.
	'app_id' => env('APP_ID'),

    'app_name' => env('APP_NAME'),

    'app_key' => env('APP_KEY'),

    // Recomendado usar cache com o driver database ou redis caso a aplicação use autobalance.
    'cache' => [
        'ativo' => env('TOKEN_CACHE',false), // Salvar token em cache

        // Chaves usadas no Cache para guardar dados do token caso ativo = true
        'token_tipo' => 'token_tipo',
        'token_validade' => 'token_validade',
        'token' => 'token',
    ],

    'acl_session_error' => 'acl_session_error',

    /*
     * Configurações de requisição para API de Autorização.
     */
    'base_url' => env('AUTORIZACAO_URL', 'http://eb-autenticacao.eba-9xmwkvhq.us-east-1.elasticbeanstalk.com/'),
    'connect_timeout' => 10,
    'timeout' => 30,
];
