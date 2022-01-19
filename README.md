### Pacote de recursos Laravel e Lumen para auxiliar na implementação da Autorização ABC.

- [**Instalação**](#Instalação)
- [**Configuração**](#Configuração)
  - [**Geral**](#Geral)
  - [**Laravel**](#Laravel)
  - [**Lumen**](#Lumen)
- [**Autenticação**](#Autenticação)
- [**ACL**](#ACL)

<br/>

## Instalação
Adicione as seguintes chaves no `composer.json` do seu projeto Laravel ou Lumen.
```JSON
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:abc-da-construcao/autorizacao-package-client.git"
    }
  ],
  "require": {
    "abc-da-construcao/autorizacao-package-client": "dev-main"
  }
}
```

Em seguida use o comando
```shell
composer update abc-da-construcao/autorizacao-package-client
```

<br/>

## Configuração

### Geral
Garanta que os valores das chaves `APP_NAME` e `APP_KEY` contidas no arquivo `.env` do seu projeto tenha sido 
cadastradas corretamente na API de Autorização. As mesmas devem ser fornecidas para cadastro da aplicação. 
Se por alguma razão esses valores modificarem no seu projeto, deve ser informado para que seja atualizado.

```shell
APP_NAME="api_pedidos-production"
APP_KEY=base64:yGRgEDlwBFxUDUTrP/N0WWjK236dFaiD91yGsaowsMM=
```

<br/>

### Laravel
Use o seguinte comando para publicar o arquivo de configuração

```shell
php artisan vendor:publish --provider="AbcDaConstrucao\AutorizacaoCliente\Providers\AutorizacaoServiceProvider"
```

Abra o arquivo `config/auth.php` e altere o driver de autenticação para `jwt` e comente 
as linhas correspondentes ao `provider`.

```PHP
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
```

<br/>

### Lumen
Copie os seguintes arquivos para o diretório `config` do seu projeto. Crie o diretório caso não exista. <br/>
> vendor/abc-da-construcao/autorizacao-package-client/config/autorizacao.php <br/>
vendor/laravel/lumen-framework/config/auth.php <br/>


Abra o arquivo `config/auth.php` e altere o driver de autenticação para `jwt`.

```PHP
'guards' => [
    'api' => ['driver' => 'jwt']
]
```

Configure o arquivo `bootstrap/app.php`.
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
$app->configure('autorizacao');

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
Em breve

<br/>

## ACL
Em breve