<?php

namespace AbcDaConstrucao\AutenticacaoPackage\Providers;

use AbcDaConstrucao\AutenticacaoPackage\AbcGenericUser;
use AbcDaConstrucao\AutenticacaoPackage\Console\Commands\SynchronizeRoutesCommand;
use AbcDaConstrucao\AutenticacaoPackage\Contracts\MergeLocalUserInterface;
use AbcDaConstrucao\AutenticacaoPackage\Facades\JWT;
use AbcDaConstrucao\AutenticacaoPackage\Http\Middleware\AclMiddleware;
use AbcDaConstrucao\AutenticacaoPackage\Services\AclService;
use AbcDaConstrucao\AutenticacaoPackage\Services\HttpClientService;
use AbcDaConstrucao\AutenticacaoPackage\Services\JWTService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Determine if the provider is deferred.
     *
     * @return bool
     */
    public function isDeferred()
    {
        return $this->defer;
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $config = __DIR__ . '/../../config/sag.php';
        $this->mergeConfigFrom($config, 'sag');
        $this->publishes([$config => $this->app->configPath('sag.php')], 'sag:config');
        $this->registerJwtAuthGuard();
        $this->registerAclMiddleware();
        $this->registerCommands();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(HttpClientService::class, function ($app) {
            return new HttpClientService($app->has('session'));
        });

        $this->app->singleton(JWTService::class, function ($app) {
            return new JWTService($app->has('session'));
        });

        $this->app->singleton(AclService::class, function () {
            return new AclService();
        });

        $this->app->singleton(AclMiddleware::class, function () {
            return new AclMiddleware();
        });

        $this->app->singleton(SynchronizeRoutesCommand::class, function () {
            return new SynchronizeRoutesCommand();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            SynchronizeRoutesCommand::class,
        ];
    }

    /**
     * @return void
     */
    protected function registerJwtAuthGuard()
    {
        $this->app['auth']->viaRequest('sag-jwt', function (Request $request) {
            // Busca token da sess??o caso exita.
            $tokenTipo = JWT::getTokenType();
            $token = JWT::getToken();

            // D?? prefer??ncia para o token passado via header
            if ($request->hasHeader('Authorization')) {
                $tokenSplit = explode(' ', $request->header('Authorization'));
                if (count($tokenSplit) == 2) {
                    $tokenTipo = $tokenSplit[0];
                    $token = $tokenSplit[1];
                }
            }

            if (!$user = JWT::validate($tokenTipo, $token)) {
                return null;
            }

            // faz merge de dados do usu??rio local
            $user = $this->mergeLocalUser($user);

            return new AbcGenericUser($user);
        });
    }

    /**
     * @param array $user
     * @return array
     */
    protected function mergeLocalUser(array $user)
    {
        $config = Config::get('sag');

        if (!empty($config['user_local_class']) && class_exists($config['user_local_class'])
            && in_array(MergeLocalUserInterface::class, class_implements($config['user_local_class']))
        ) {
            $userLocal = new $config['user_local_class'];
            $dataMerge = $userLocal->getUserFromMerge($user['id']);
            $filterKeys = [
                'id', 'name', 'username', 'email', 'active', 'email_verifield_at', 'password', 'created_by',
                'updated_by', 'active', 'root', 'remember_token', 'created_at', 'updated_at', 'apps'
            ];

            foreach ($dataMerge as $key => $value) {
                if (!in_array($key, [$filterKeys])) {
                    $user[$key] = $value;
                }
            }
        }

        return $user;
    }

    /**
     * @return void
     */
    protected function registerAclMiddleware()
    {
        if (class_exists('Laravel\Lumen\Application')) {
            $this->app->middleware([AclMiddleware::class]);
        } else {
            $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
            $kernel->pushMiddleware(AclMiddleware::class);
        }
    }

    /**
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SynchronizeRoutesCommand::class,
            ]);
        }
    }
}
