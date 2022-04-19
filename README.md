### Pacote de recursos Laravel e Lumen para auxiliar na implementação da Autenticação/Autorização ABC.

- [**Instalação**](#Instalação)
- [**Configuração**](#Configuração)
  - [**Geral**](#Geral)
  - [**Laravel**](#Laravel)
  - [**Lumen**](#Lumen)
- [**Autenticação**](#Autenticação)
  - [Método auxiliar para autenticação.](#método-auxiliar-para-autenticação)
  - [Método de acesso aos dados do usuário.](#método-de-acesso-aos-dados-do-usuário)
  - [Dados complementares para o usuário logado.](#dados-complementares-para-o-usuário-logado)
  - [Método auxiliar para logout.](#método-auxiliar-para-logout)
  - [Método auxiliar para verificar permissão de acesso.](#método-auxiliar-para-verificar-permissão-de-acesso)
- [**Autorização**](#Autorização)
  - [Sincronizar rotas da aplicação com a API de Autorização.](#sincronizar-rotas-da-aplicação-com-a-api-de-autorização)

<br/>

## Instalação
Este repositório é privado, por tanto você deve criar uma chave SSH no servidor
e ter um Token de acesso pessoal para conseguir instalar esse pacote.

> Veja aqui: <br>
> - <a href="https://docs.github.com/pt/authentication/connecting-to-github-with-ssh/adding-a-new-ssh-key-to-your-github-account" target="_blank">Adicionar uma nova chave SSH a sua conta GitHub</a>
> - <a href="https://docs.github.com/pt/authentication/keeping-your-account-and-data-secure/creating-a-personal-access-token" target="_blank">Criar um token de acesso pessoal a sua conta GitHub</a>

```
// Criando uma chave SSH no Servidor

cd ~/.ssh
ssh-keygen -t rsa -b 4096 -C "your_email@example.com"
```
Após gerar a chave adicione a mesma a sua conta github.

Adicione as seguintes chaves no `composer.json` do seu projeto Laravel ou Lumen.
```
{
  //...
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:abc-da-construcao/autenticacao-package.git"
    }
  ],
  //...
}
```

Em seguida use o comando abaixo. Será solicitado seu Personal Token a primeira vez que instalar o pacote.
```shell
composer require abc-da-construcao/autenticacao-package
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

// ...
$app->withFacades();
// ...

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

// ...
$app->configure('auth');
// ...

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

// ...
$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
]);
// ...

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

// ...
// $app->register(App\Providers\AuthServiceProvider::class);
$app->register(AbcDaConstrucao\AutenticacaoPackage\Providers\AuthServiceProvider::class);
// ...
```

<br/>

## Autenticação

### Método auxiliar para autenticação.
`Http::loginRequest($username, $base64_password)`. 

**Exemplos em API Lumen**

```PHP
<?php

use AbcDaConstrucao\AutenticacaoPackage\Facades\Http;
use Illuminate\Http\Request;

$router->post('/login', function (Request $request) {
    // A senha deve ser enviada com base64 encode para API de Autenticação. 
    $response = Http::loginRequest($request->username, base64_encode($request->password));

    // Devolve o token caso status 200 ou o erro específico 
    // Ver abaixo resultado esperado do $response.
    return response()->json($response['data'], $response['status']);
});
```

<br>

**Exemplo em Frontend Laravel/Lumen** <br>

O método `Http::loginRequest()` também salva o token em sessão caso exista a classe `Illuminate\Session\SessionManager`,
facilitando o manuseio do mesmo e mantendo o usuário logado enquanto o token for válido.
 
```PHP
<?php

use AbcDaConstrucao\AutenticacaoPackage\Facades\Http;
use Illuminate\Http\Request;

Route::post('/login', function (Request $request) {
    // A senha deve ser enviada com base64 encode para API de Autenticação.
    $response = Http::loginRequest($request->username, base64_encode($request->password));

    // token obtido e salvo em sessão.
    // Redireciona o usuário a página desejada.
    if ($response['status'] == 200) {
        return redirect()->route('home');
    }

    // Se o token não for emitido retorna o usuário a página de login com os erros.
    return back()->with('errors', $response['data']['message']);
});
```
Posteriormente poderá acessar o token e passar nas requisições seguintes para o backend da seguinte forma.

```PHP
use Illuminate\Support\Facades\Config;

$token = session()->get(Config::get('auth_abc.session.token'));
$tokenType = session()->get(Config::get('auth_abc.session.token_type'));
$tokenValidate = session()->get(Config::get('auth_abc.session.token_validate'));
```

<br>

**Resultado esperado em `$response`.**

```PHP
// statuscode 200
[
    'status' => 200,
    'data' => [
        'token_tipo' => 'Bearer',
        'token_validade' => '2022-02-11T21:49:35-03:00',
        'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTY0NDYwODk3NSwiZXhwIjoxNjQ0NjI2OTc1LCJuYmYiOjE2NDQ2MDg5NzUsImp0aSI6IlhXT0M5QWRDcGZWZW5WWGQiLCJzdWIiOjMsInBydiI6ImYzNzVlYWVkMGM2ZjE2YjJjOWUyYmY1NzE2YzUwMTZiNzUwZjI1NjcifQ.5YrwxDYLNzpt1xnnX5jyVtoEUIMkYfrpDykqNsLTY0g',
    ]
]

// statuscode 401
[
  "status" => 401,
  "data" => [
    "message" => "Credenciais inválidas."
  ]
]

// statuscode 401
[
  "status" => 401,
  "data" => [
    "message" => "Usuário desativado."
  ]
]

// statuscode 422
[
  "status" => 422,
  "data" => [
    "message" => "The given data was invalid."
    "errors" => [
        "username" => "O campo username é obrigatório",
        "password" => "O campo password é obrigatório"
    ]
  ]
]
```

### Método de acesso aos dados do usuário.
Após autenticação, os dados do usuário estarão disponíveis na facade `Auth` do Laravel ou Lumen. Tenham atenção para especificar o guardião caso não seja o default. Exemplos de acesso.
```PHP
use Illuminate\Support\Facades\Auth;

$user = Auth::user(); // API Lumen ou Frontend Laravel 
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
`\AbcDaConstrucao\AutenticacaoPackage\Contracts\MergeLocalUserInterface`. Essa classe deve conter o método `getUserFromMerge(int $abcUserId)` que retorna um array que será mergeado com os dados do usuário logado. Como parâmetro, o método recebe o id do usuário logado para facilitar o relacionamento com os dados locais. Exemplo de implementação.

```PHP
<?php

namespace App\Repositories;

use AbcDaConstrucao\AutenticacaoPackage\Contracts\MergeLocalUserInterface;

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

### Método auxiliar para logout

`Http::logoutRequest($tokenTipo, $token);` <br>

**Aplicações API**

```PHP
$router->post('/logout', ['as' => 'logout', function (Request $request) {
    $header = $request->header('Authorization');
    $token = explode(' ', $header);
    $response = Http::logoutRequest($token[0], $token[1]);
    
    return response()->json($response['data'], $response['status']);
}]);
```

<br>

**Para aplicações Frontend Laravel/Lumen** <br>

Será removido as chaves de sessão que contém o token.

```PHP
Route::post('/logout', ['as' => 'logout', function (Request $request) {
    $response = Http::logoutRequest();

    // Redireciona a página desejada.
    if ($response['status'] == 200) {
        return redirect()->route('login');
    }
    
    // Retorna erro caso exista.
    return back()->with('error', $response['data']);
}]);
```

<br>

**Resultado esperado em $response.** <br>
```PHP
[
    'status' => 200,
    'data' => [
        'message' => 'Desconectado com sucesso.'
    ]
]
```

### Método auxiliar para verificar permissão de acesso
O método `ACL::hasRouteAccess(string $routeNameOrUri)` recebe o `nome` ou a `uri` de uma rota e retorna boolean se o usuário
atual tem permissão de acesso. Pode ser aplicado em diversas situações de uso.

```PHP
// Lumen - routes/web.php
$router->get('/api/profile', ['as' => 'api.profile', function (Request $request) use ($router) {
    // ...
}]);

use AbcDaConstrucao\AutenticacaoPackage\Facades\ACL;
// Nome da rota.
ACL::hasRouteAccess('api.profile');

// URI da rota
ACL::hasRouteAccess('/api/profile');
```

<br>

## Autorização

### Sincronizar rotas da aplicação com a API de Autorização.
Após criar ou atualizar as rotas da aplicação deve-se usar o command de sincronização.

```
php artisan abc-auth:sync-routes
```