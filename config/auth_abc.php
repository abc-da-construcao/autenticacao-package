<?php

return [
    // Nome e chave da aplicação cadastrada na API de Autorização.
    'app_name' => env('APP_NAME'),
    'app_key' => env('APP_KEY'),

    /*
     * Classe que usa a interface \AbcDaConstrucao\AutenticacaoPackage\Contracts\MergeLocalUserInterface para fazer
     * merge de dados do usuário logado com usuário da aplicação local
     *
     * Exemplo config direto no arquivo: 'user_local_class' => \App\Repositories\UserRepository::class,
     * Exemplo config no arquivo .env: USER_LOCAL_CLASS=\App\Repositories\UserRepository
     *
     * Valor default: null
    */
    'user_local_class' => env('USER_LOCAL_CLASS'),

    // Recomendado usar cache com o driver database ou redis caso a aplicação use autobalance.
    'cache' => [
        'ativo' => env('TOKEN_CACHE', false), // Salvar token em cache

        // Chaves usadas no Cache para guardar dados do token caso ativo = true
        'token_tipo' => 'token_tipo',
        'token_validade' => 'token_validade',
        'token' => 'token',
    ],

    // Para uso de frontend. Chave de sessão contendo a mensagem de não autorizado.
    // https://laravel.com/docs/8.x/session#flash-data
    'acl_session_error' => 'acl_session_error',

    /*
     * Configurações de requisição para API de Autorização.
    */
    'base_url' => env('API_AUTORIZACAO_URL', 'http://eb-autenticacao.eba-9xmwkvhq.us-east-1.elasticbeanstalk.com/api/'),
    'connect_timeout' => 10,
    'timeout' => 30,
];
