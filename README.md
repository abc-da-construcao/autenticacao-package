### Pacote de recursos Laravel e Lumen para auxiliar na implementação da Autorização ABC.

- [**Instalação**](#Instalação)
- [**Configuração**](#Configuração)
  - [**Geral**](#Geral)
  - [**Laravel**](#Laravel)
  - [**Lumen**](#Lumen)
- [**Autenticação**](#Autenticação)
  - [Método auxiliar para autenticação.](#Método-auxiliar-para-autenticação.)
  - [Método de acesso aos dados do usuário.](#Método-de-acesso-aos-dados-do-usuário.)
  - [Dados complementares para o usuário logado.](#Dados-complementares-para-o-usuário-logado.)
- [**Autorização**](#Autorização)
  - [Sincronizar rotas da aplicação com a API de Autorização.](#Sincronizar-rotas-da-aplicação-com-a-API-de-Autorização.)

<br/>

## Instalação
Adicione as seguintes chaves no `composer.json` do seu projeto Laravel ou Lumen.
```
{
  //...
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:abc-da-construcao/autorizacao-package-client.git"
    }
  ],
  "require": {
    //...
    "abc-da-construcao/autorizacao-package-client": "^1.0"
  }
  //...
}
```

Em seguida use o comando
```shell
composer update abc-da-construcao/autorizacao-package-client
```

<br/>

## Configuração

### Geral
Preencha as chaves `APP_NAME` e `APP_KEY` contidas no arquivo `.env` do projeto conforme 
cadastro na API de Autenticação/Autorização.

```
APP_NAME="api_pedidos-production"
APP_KEY=Gb4E7xqR74Pat9gefb7nidcWFZNW8S66
```

<br/>

### Laravel

Abra o arquivo `config/auth.php` e altere o driver de autenticação para `jwt` e comente 
as linhas correspondentes ao `provider`.

```PHP
//...
'guards' => [
    'web' => [
        'driver' => 'jwt',
        //'provider' => 'users',
    ],
    'api' => [
        'driver' => 'jwt',
        //'provider' => 'users',
    ],
]
//...
```

<br/>

### Lumen
Copie os seguintes arquivos para o diretório `config` do seu projeto. Crie o diretório caso não exista. <br/>

> vendor/laravel/lumen-framework/config/auth.php <br/>


Abra o arquivo `config/auth.php` e altere o driver de autenticação para `jwt`.

```PHP
'guards' => [
    'api' => ['driver' => 'jwt']
]
```

Garanta que no arquivo `bootstrap/app.php` exista as seguintes configurações.
```PHP
/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app->withFacades();

/*
|--------------------------------------------------------------------------
| Register Config Files
|--------------------------------------------------------------------------
|
| Now we will register the "app" configuration file. If the file exists in
| your configuration directory it will be loaded; otherwise, we'll load
| the default version. You may register other files below as needed.
|
*/

$app->configure('auth');

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(AbcDaConstrucao\AutorizacaoCliente\Providers\AutorizacaoServiceProvider::class);
```

<br/>

## Autenticação

### Método auxiliar para autenticação.
Auxilia as aplicações frontend a realizar login com o mesmo comportamento atual.
`Http::loginRequest($username, $base64_password)`. 

**Configuração no arquivo `.env`:**
```
TOKEN_CACHE=true
```

**Exemplo de uso:**
```PHP
// routes/web.php
<?php

use AbcDaConstrucao\AutorizacaoCliente\Facades\Http;
use Illuminate\Http\Request;

Route::post('/login', function (Request $request) {
    $response = Http::loginRequest($request->username, base64_encode($request->password));

    if (isset($response['token'])) {
        return redirect()->route('home');
    }

    return back()->with('errors', $response['message']);
});
```

resultado esperado em `$response`.
```PHP
// statuscode 200
[
  'token_tipo' => 'Bearer',
  'token_validade' => '2022-02-11T21:49:35-03:00',
  'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTY0NDYwODk3NSwiZXhwIjoxNjQ0NjI2OTc1LCJuYmYiOjE2NDQ2MDg5NzUsImp0aSI6IlhXT0M5QWRDcGZWZW5WWGQiLCJzdWIiOjMsInBydiI6ImYzNzVlYWVkMGM2ZjE2YjJjOWUyYmY1NzE2YzUwMTZiNzUwZjI1NjcifQ.5YrwxDYLNzpt1xnnX5jyVtoEUIMkYfrpDykqNsLTY0g',
]

// statuscode 401
['message' => 'Credenciais inválidas.']

// statuscode 401
['message' => 'Usuário desativado.']
```

### Método de acesso aos dados do usuário.
Após autenticação, os dados do usuário estarão disponíveis na facade `Auth` do Laravel ou Lumen. Tenham atenção para especificar o guardião caso não seja o default. Exemplos de acesso.
```PHP
use Illuminate\Support\Facades\Auth;

$user = Auth::user(); // Frontend Laravel ou API Lumen
$user = Auth::guard('api')->user(); // API Laravel

dd($user->toArray());
// result
[
  'id' => 3,
  'name' => 'Nome Sobrenome',
  'username' => 'nome.sobrenome',
  'email' => 'nome.sobrenome@abcdaconstrucao.com.br',
  'email_verified_at' => '2022-02-09 18:04:12',
  'active' => '1',
  'root' => '0',
  'created_by' => '1',
  'updated_by' => '1',
  'created_at' => '2022-02-09 21:04:12',
  'updated_at' => '2022-02-09 21:04:12',
  'apps' => [
    0 => [
      'id' => 1,
      'name' => 'API_PEDIDOS_SERVER-DEV',
      'url' => 'http://localhost:3000',
      'created_by' => '1',
      'updated_by' => '1',
      'active' => '1',
      'created_at' => '2022-02-09 21:04:13',
      'updated_at' => '2022-02-09 21:04:13',
      'super_admin' => '1',
      'groups' => [
        0 => [
          'id' => 1,
          'app_id' => '1',
          'name' => 'Farming',
          'description' => 'Usuários do grupo Farming API_PEDIDOS_SERVER-DEV',
          'active' => '1',
          'created_by' => '3',
          'updated_by' => '3',
          'created_at' => '2022-02-09 21:04:13',
          'updated_at' => '2022-02-09 21:04:13',
          'permissions' => [
            0 => [
              'id' => 10,
              'app_id' => '1',
              'method' => 'GET',
              'uri' => '/',
              'name' => 'home',
              'public' => '0',
            ],
          ],
        ],
      ],
    ],
    1 => [
      'id' => 2,
      'name' => 'API_PEDIDOS_FRONT-DEV',
      'url' => 'http://localhost:3100',
      'created_by' => '1',
      'updated_by' => '1',
      'active' => '1',
      'created_at' => '2022-02-09 21:04:13',
      'updated_at' => '2022-02-09 21:04:13',
      'super_admin' => '0',
      'groups' => [
        0 => [
          'id' => 2,
          'app_id' => '2',
          'name' => 'Farming',
          'description' => 'Usuários do grupo Farming API_PEDIDOS_FRONT-DEV',
          'active' => '1',
          'created_by' => '3',
          'updated_by' => '3',
          'created_at' => '2022-02-09 21:04:13',
          'updated_at' => '2022-02-09 21:04:13',
          'permissions' => [
            0 => [
              'id' => 4,
              'app_id' => '2',
              'method' => 'GET|HEAD',
              'uri' => '/home',
              'name' => 'home',
              'public' => '0',
            ],
          ],
        ],
      ],
    ],
  ],
]
```

### Dados complementares para o usuário logado.
Caso em sua aplicação seja necessário adicionar mais campos para o usuário logado, basta
informar nas configurações uma classe que implemente a interface 
`\AbcDaConstrucao\AutorizacaoCliente\Contracts\MergeLocalUserInterface`. Essa classe deve conter o método `getUserFromMerge(int $abcUserId)` que retorna um array que será mergeado com os dados do usuário logado. Como parâmetro, o método recebe o id do usuário logado para facilitar o relacionamento com os dados locais. Exemplo de implementação.

```PHP
<?php

namespace App\Repositories;

use AbcDaConstrucao\AutorizacaoCliente\Contracts\MergeLocalUserInterface;

class UserRepository implements MergeLocalUserInterface
{
    /**
     * @param int $abcUserId
     * @return array
     */
    public function getUserFromMerge(int $abcUserId)
    {
        $userLocal = User::getByAbcId($abcUserId);

        return [
            'cpf' => $userLocal->cpf
        ];
    }
}
```
Abra o arquivo `.env` e adicione o namespace da classe na chave abaixo.

```
USER_LOCAL_CLASS=\App\Repositories\UserRepository
```

Agora ao acessar a facade `Auth` as chaves adicionais do usuário estarão acessíveis.

```PHP
[
  'id' => 3,
  'name' => 'Nome Sobrenome',
  'username' => 'nome.sobrenome',
  'email' => 'nome.sobrenome@abcdaconstrucao.com.br',
  'email_verified_at' => '2022-02-09 18:04:12',
  'active' => '1',
  'root' => '0',
  'created_by' => '1',
  'updated_by' => '1',
  'created_at' => '2022-02-09 21:04:12',
  'updated_at' => '2022-02-09 21:04:12',
  'apps' => [...],
  'cpf' => '03614568953' // dado adicionado pela aplicação local
]
```

<br/>

## Autorização

### Sincronizar rotas da aplicação com a API de Autorização.
Após criar ou atualizar as rotas da aplicação deve-se usar o command de sincronização.

```
php artisan abc-auth:sync-routes
```