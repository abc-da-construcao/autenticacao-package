<?php

namespace AbcDaConstrucao\AutorizacaoCliente\Providers;

use AbcDaConstrucao\AutorizacaoCliente\Facades\Http;
use AbcDaConstrucao\AutorizacaoCliente\Facades\JWT;
use AbcDaConstrucao\AutorizacaoCliente\Services\HttpClientService;
use AbcDaConstrucao\AutorizacaoCliente\Services\JWTService;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AutorizacaoServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../../config/autorizacao.php';
        $this->mergeConfigFrom($configPath, 'autorizacao');
        $this->publishes([$configPath => config_path('acl.php')], 'autorizacao:config');

        $this->registerAuthGuard();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(HttpClientService::class, function ($app) {
            return new HttpClientService;
        });

        $this->app->singleton(JWTService::class, function ($app) {
            return new JWTService;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Http::class, JWT::class];
    }

    protected function registerAuthGuard()
    {
        $this->app['auth']->viaRequest('jwt', function (Request $request) {
            if (empty($request->header('Authorization'))) {
                return null;
            }

            $tokenSplit = explode(' ', $request->header('Authorization'));

            if (!JWT::validate($tokenSplit[0], $tokenSplit[1])) {
                return null;
            }

            $user = JWT::getUser($tokenSplit[1]);

            return new GenericUser($user);
        });
    }
}
