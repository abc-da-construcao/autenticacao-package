#### Pacote de recursos Laravel e Lumen para auxiliar na implementação do Sistema de Autenticação Global (SAG).

Este pacote para Lumen e Laravel fornece um driver customizado de autenticação/autorização, middleware, 
Facade e commands para uso do Sistema de Autenticação Global nos padrões ABC. 

<br>

- [**Instalação**](#Instalação)
- [**Configuração**](#Configuração)
  - [**Geral**](#Geral)
  - [**Laravel**](#Laravel)
  - [**Lumen**](#Lumen)
- [**Protegendo Rotas**](#protegendo-rotas)
- [**Autenticação**](#Autenticação)
  - [Método auxiliar para autenticação.](#método-auxiliar-para-autenticação)
  - [Método de acesso aos dados do usuário.](#método-de-acesso-aos-dados-do-usuário)
  - [Dados complementares para o usuário logado.](#dados-complementares-para-o-usuário-logado)
  - [Método auxiliar para logout.](#método-auxiliar-para-logout)
  - [Método auxiliar para verificar permissão de acesso.](#método-auxiliar-para-verificar-permissão-de-acesso)
- [**Autorização**](#Autorização)
  - [Sincronizar rotas da aplicação com a API de Autenticação.](#sincronizar-rotas-da-aplicação-com-a-api-de-autenticação)

<br/>

## Instalação
Este repositório é privado, portanto você deve criar ou informar uma chave rsa para acesso SSH
e ter um Token de acesso pessoal para conseguir instalar esse pacote.

> Veja mais informações: <br>
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
cadastro no SAG.

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
    // ...
    'sag' => [
        'driver' => 'sag-jwt'
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
    // ...
    'sag' => ['driver' => 'sag-jwt'],
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

## Protegendo Rotas
Apesar de apresentar a mesma característica de proteção padrão de rotas, o pacote verifica 
não só se o token JWT é válido mas também verifica as permissões que o usuário tem na aplicação
realizando também a função de ACL.

Exemplo de grupo de rotas protegidas por autenticação.

```PHP
// Lumen
$router->group(['middleware' => ['auth:sag']], function () use ($router) {
    // Routes
});

// Laravel API
Route::middleware(['auth:sag'])->group(function () {
    // Routes
});
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

$token = session()->get(Config::get('sag.session.token'));
$tokenType = session()->get(Config::get('sag.session.token_type'));
$tokenValidate = session()->get(Config::get('sag.session.token_validate'));
```

<br>

**Resultado esperado em `$response`.**

```PHP
// statuscode 200
[
    "status" => 200,
    "data" => [
        "token_tipo" => "Bearer",
        "token_validade" =>  "2022-04-28T17:49:21-03:00",
        "token" => "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJodHRwOlwvXC9zYWctc2VydmVyLmxvY2FsXC9hdXRoXC9sb2dpbiIsImlhdCI6MTY1MTE3NTIzOCwiZXhwIjoxNjUxMTc4ODM4LCJqaXQiOiJqd3RfNSIsInN1YiI6NX0.aSYc0iMkPzVb2EluK7rtXwnkjfv0TdjFFFHuSvd7VMQ",
        "payload" => "eyJpZCI6NSwibmFtZSI6IlRhbGxlcyBHYXplbCIsInVzZXJuYW1lIjoidGFsbGVzIiwiZW1haWwiOiJ0YWxsZXMuZ2F6ZWxAYWJjZGFjb25zdHJ1Y2FvLmNvbS5iciIsImVtYWlsX3ZlcmlmaWVkX2F0IjpudWxsLCJwYXNzd29yZCI6IiQyeSQxMCRnTGF0dk9zcmYwb25PUElodzV1N2hlRTFlTXNZb1Bta3M1cU5EMVdpc0x6bmZnSkdYNHUuZSIsImFjdGl2ZSI6MSwicm9vdCI6MCwiZXhwaXJlIjoxLCJjcmVhdGVkX2J5IjoxLCJ1cGRhdGVkX2J5IjoxLCJyZW1lbWJlcl90b2tlbiI6bnVsbCwiY3JlYXRlZF9hdCI6IjIwMjItMDQtMjggMTU6MDI6NDQiLCJ1cGRhdGVkX2F0IjoiMjAyMi0wNC0yOCAxNTowMzowMyIsImFwcHMiOlt7ImlkIjoxLCJuYW1lIjoiYXBpX3BlZGlkb3Nfc2VydmVyLXByb2R1Y3Rpb24iLCJ1cmwiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMSIsImFjdGl2ZSI6MSwic3VwZXJfYWRtaW4iOjAsImNyZWF0ZWRfYnkiOjEsInVwZGF0ZWRfYnkiOjEsImNyZWF0ZWRfYXQiOiIyMDIyLTA0LTI4IDEwOjM4OjU2IiwidXBkYXRlZF9hdCI6IjIwMjItMDQtMjggMTA6Mzg6NTYiLCJncm91cHMiOlt7ImlkIjoyLCJhcHBfaWQiOjEsIm5hbWUiOiJGQVJNIiwiZGVzY3JpcHRpb24iOiJHcnVwbyBkbyBSYW1vbiIsImFjdGl2ZSI6MSwiY3JlYXRlZF9ieSI6MSwidXBkYXRlZF9ieSI6MSwiY3JlYXRlZF9hdCI6IjIwMjItMDQtMjggMTU6MDI6MDciLCJ1cGRhdGVkX2F0IjoiMjAyMi0wNC0yOCAxNTowMjowNyIsInBlcm1pc3Npb25zIjpbeyJhcHBfaWQiOjEsImdyb3VwX2lkIjoyLCJpZCI6NCwibWV0aG9kIjoiUE9TVCIsInVyaSI6IlwvbG9nb3V0IiwibmFtZSI6ImxvZ291dCIsInB1YmxpYyI6MH0seyJhcHBfaWQiOjEsImdyb3VwX2lkIjoyLCJpZCI6MywibWV0aG9kIjoiR0VUIiwidXJpIjoiXC9wcm9maWxlIiwibmFtZSI6InByb2ZpbGUiLCJwdWJsaWMiOjB9XX1dLCJyb3V0ZXMiOlt7ImlkIjoxLCJhcHBfaWQiOjEsIm1ldGhvZCI6IlBPU1QiLCJ1cmkiOiJcL2xvZ2luIiwibmFtZSI6ImxvZ2luIiwicHVibGljIjoxfSx7ImlkIjoyLCJhcHBfaWQiOjEsIm1ldGhvZCI6IkdFVCIsInVyaSI6Ilwvcm90YS1wdWJsaWNhIiwibmFtZSI6InJvdGEtcHVibGljYSIsInB1YmxpYyI6MX0seyJpZCI6MywiYXBwX2lkIjoxLCJtZXRob2QiOiJHRVQiLCJ1cmkiOiJcL3Byb2ZpbGUiLCJuYW1lIjoicHJvZmlsZSIsInB1YmxpYyI6MH0seyJpZCI6NCwiYXBwX2lkIjoxLCJtZXRob2QiOiJQT1NUIiwidXJpIjoiXC9sb2dvdXQiLCJuYW1lIjoibG9nb3V0IiwicHVibGljIjowfSx7ImlkIjo1LCJhcHBfaWQiOjEsIm1ldGhvZCI6IkdFVCIsInVyaSI6IlwvIiwibmFtZSI6ImhvbWUiLCJwdWJsaWMiOjB9XX0seyJpZCI6MywibmFtZSI6ImFwaV9wZWRpZG9zX2Zyb250LXByb2R1Y3Rpb24iLCJ1cmwiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMiIsImFjdGl2ZSI6MSwic3VwZXJfYWRtaW4iOjAsImNyZWF0ZWRfYnkiOjEsInVwZGF0ZWRfYnkiOjEsImNyZWF0ZWRfYXQiOiIyMDIyLTA0LTI4IDE1OjMyOjI5IiwidXBkYXRlZF9hdCI6IjIwMjItMDQtMjggMTU6MzI6MjkiLCJncm91cHMiOlt7ImlkIjozLCJhcHBfaWQiOjMsIm5hbWUiOiJGQVJNIiwiZGVzY3JpcHRpb24iOm51bGwsImFjdGl2ZSI6MSwiY3JlYXRlZF9ieSI6MSwidXBkYXRlZF9ieSI6MSwiY3JlYXRlZF9hdCI6IjIwMjItMDQtMjggMTU6MzM6NDMiLCJ1cGRhdGVkX2F0IjoiMjAyMi0wNC0yOCAxNTozMzo0MyIsInBlcm1pc3Npb25zIjpbeyJhcHBfaWQiOjMsImdyb3VwX2lkIjozLCJpZCI6MTEsIm1ldGhvZCI6IkdFVHxIRUFEIiwidXJpIjoiXC9hcGlcL2hvbWUiLCJuYW1lIjoiYXBpLmhvbWUiLCJwdWJsaWMiOjB9LHsiYXBwX2lkIjozLCJncm91cF9pZCI6MywiaWQiOjksIm1ldGhvZCI6IlBPU1QiLCJ1cmkiOiJcL2FwaVwvbG9nb3V0IiwibmFtZSI6ImFwaS5sb2dvdXQiLCJwdWJsaWMiOjB9LHsiYXBwX2lkIjozLCJncm91cF9pZCI6MywiaWQiOjEwLCJtZXRob2QiOiJHRVR8SEVBRCIsInVyaSI6IlwvYXBpXC9wcm9maWxlIiwibmFtZSI6ImFwaS5wcm9maWxlIiwicHVibGljIjowfSx7ImFwcF9pZCI6MywiZ3JvdXBfaWQiOjMsImlkIjoxNSwibWV0aG9kIjoiR0VUfEhFQUQiLCJ1cmkiOiJcL2hvbWUiLCJuYW1lIjoiaG9tZSIsInB1YmxpYyI6MH0seyJhcHBfaWQiOjMsImdyb3VwX2lkIjozLCJpZCI6MTYsIm1ldGhvZCI6IkdFVHxIRUFEIiwidXJpIjoiXC9sb2dvdXQiLCJuYW1lIjoibG9nb3V0IiwicHVibGljIjowfV19XSwicm91dGVzIjpbeyJpZCI6NiwiYXBwX2lkIjozLCJtZXRob2QiOiJHRVR8SEVBRCIsInVyaSI6Ilwvc2FuY3R1bVwvY3NyZi1jb29raWUiLCJuYW1lIjpudWxsLCJwdWJsaWMiOjF9LHsiaWQiOjcsImFwcF9pZCI6MywibWV0aG9kIjoiR0VUfEhFQUQiLCJ1cmkiOiJcL2FwaVwvcHVibGljYSIsIm5hbWUiOiJhcGkucHVibGljYSIsInB1YmxpYyI6MX0seyJpZCI6OCwiYXBwX2lkIjozLCJtZXRob2QiOiJQT1NUIiwidXJpIjoiXC9hcGlcL2xvZ2luIiwibmFtZSI6ImFwaS5sb2dpbiIsInB1YmxpYyI6MX0seyJpZCI6OSwiYXBwX2lkIjozLCJtZXRob2QiOiJQT1NUIiwidXJpIjoiXC9hcGlcL2xvZ291dCIsIm5hbWUiOiJhcGkubG9nb3V0IiwicHVibGljIjowfSx7ImlkIjoxMCwiYXBwX2lkIjozLCJtZXRob2QiOiJHRVR8SEVBRCIsInVyaSI6IlwvYXBpXC9wcm9maWxlIiwibmFtZSI6ImFwaS5wcm9maWxlIiwicHVibGljIjowfSx7ImlkIjoxMSwiYXBwX2lkIjozLCJtZXRob2QiOiJHRVR8SEVBRCIsInVyaSI6IlwvYXBpXC9ob21lIiwibmFtZSI6ImFwaS5ob21lIiwicHVibGljIjowfSx7ImlkIjoxMiwiYXBwX2lkIjozLCJtZXRob2QiOiJHRVR8SEVBRCIsInVyaSI6IlwvIiwibmFtZSI6InN0YXJ0IiwicHVibGljIjoxfSx7ImlkIjoxMywiYXBwX2lkIjozLCJtZXRob2QiOiJHRVR8SEVBRCIsInVyaSI6IlwvbG9naW4iLCJuYW1lIjoibG9naW4iLCJwdWJsaWMiOjF9LHsiaWQiOjE0LCJhcHBfaWQiOjMsIm1ldGhvZCI6IlBPU1QiLCJ1cmkiOiJcL2xvZ2luIiwibmFtZSI6InBvc3QubG9naW4iLCJwdWJsaWMiOjF9LHsiaWQiOjE1LCJhcHBfaWQiOjMsIm1ldGhvZCI6IkdFVHxIRUFEIiwidXJpIjoiXC9ob21lIiwibmFtZSI6ImhvbWUiLCJwdWJsaWMiOjB9LHsiaWQiOjE2LCJhcHBfaWQiOjMsIm1ldGhvZCI6IkdFVHxIRUFEIiwidXJpIjoiXC9sb2dvdXQiLCJuYW1lIjoibG9nb3V0IiwicHVibGljIjowfV19XX0="
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

$user = Auth::guard('sag')->user();

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
  'expire' => '1',
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

### Sincronizar rotas da aplicação com a API de Autenticação.
Após criar ou atualizar as rotas da aplicação deve-se usar o command de sincronização.

```
php artisan abc-sag:sync-routes
```