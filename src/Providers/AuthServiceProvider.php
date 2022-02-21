<?php

namespace AbcDaConstrucao\AutenticacaoPackage\Providers;

use AbcDaConstrucao\AutenticacaoPackage\AbcGenericUser;
use AbcDaConstrucao\AutenticacaoPackage\Console\Commands\SyncronizeRoutesCommand;
use AbcDaConstrucao\AutenticacaoPackage\Contracts\MergeLocalUserInterface;
use AbcDaConstrucao\AutenticacaoPackage\Facades\ACL;
use AbcDaConstrucao\AutenticacaoPackage\Facades\Http;
use AbcDaConstrucao\AutenticacaoPackage\Facades\JWT;
use AbcDaConstrucao\AutenticacaoPackage\Http\Middleware\AclMiddleware;
use AbcDaConstrucao\AutenticacaoPackage\Services\AclService;
use AbcDaConstrucao\AutenticacaoPackage\Services\HttpClientService;
use AbcDaConstrucao\AutenticacaoPackage\Services\JWTService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $config = __DIR__ . '/../../config/auth_abc.php';
        $this->mergeConfigFrom($config, 'auth_abc');
        $this->publishes([$config => $this->app->configPath('auth_abc.php')], 'abc_auth:config');
        $this->registerJwtAuthGuard();
        $this->registerAclMiddleware();
        $this->registerCommands();
        $this->regiterGates();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(HttpClientService::class, function ($app) {
            return new HttpClientService();
        });

        $this->app->singleton(JWTService::class, function ($app) {
            return new JWTService();
        });

        $this->app->singleton(AclService::class, function ($app) {
            return new AclService();
        });

        $this->app->singleton(AclMiddleware::class, function ($app) {
            return new AclMiddleware();
        });

        $this->app->singleton(SyncronizeRoutesCommand::class, function ($app) {
            return new SyncronizeRoutesCommand();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Http::class, JWT::class, ACL::class];
    }

    /**
     * @return void
     */
    protected function registerJwtAuthGuard()
    {
        $this->app['auth']->viaRequest('jwt', function (Request $request) {
            $tokenTipo = JWT::getTokenType();
            $token = JWT::getToken();

            if ($request->header('Authorization')) {
                $tokenSplit = explode(' ', $request->header('Authorization'));
                $tokenTipo = $tokenSplit[0];
                $token = $tokenSplit[1];
            }

            if (!$user = JWT::validate($tokenTipo, $token)) {
                return null;
            }

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
        $config = Config::get('auth_abc');

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
        if ($this->isLumen()) {
            $this->app->middleware([AclMiddleware::class]);
        } else {
            $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);
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
                SyncronizeRoutesCommand::class,
            ]);
        }
    }

    /**
     * @return void
     */
    protected function regiterGates()
    {
        try {
            foreach (ACL::getMapRoutes() as $route) {
                $rule = $route->name ?? $route->uri;

                Gate::define($rule, function ($user) use ($route) {
                    if (empty($user) && !$route->public) {
                        return false;
                    } elseif (empty($user) && $route->public && ACL::appIsActive()) {
                        return true;
                    }

                    return Acl::validate($route, $user);
                });
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * @return bool
     */
    protected function isLumen()
    {
        return class_exists('Laravel\Lumen\Application');
    }
}
