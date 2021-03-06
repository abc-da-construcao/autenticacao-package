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

    // Para uso no frontend.
    'session' => [
        // Chaves usadas para guardar dados do token caso exita sessão no app.
        'token_type' => 'token_type',
        'token_validate' => 'token_validate',
        'token' => 'token',
    ],

    /*
     * Configurações de requisição para API de Autorização.
    */
    'base_url' => env('SAG_URL', 'https://sag-backend.digitalabc.com.br/api/'),
    'connect_timeout' => 10,
    'timeout' => 30,
];
