<?php

namespace AbcDaConstrucao\AutorizacaoCliente\Providers;

use AbcDaConstrucao\AutorizacaoCliente\AbcGenericUser;
use AbcDaConstrucao\AutorizacaoCliente\Console\Commands\SyncronizeRoutesCommand;
use AbcDaConstrucao\AutorizacaoCliente\Facades\ACL;
use AbcDaConstrucao\AutorizacaoCliente\Facades\Http;
use AbcDaConstrucao\AutorizacaoCliente\Facades\JWT;
use AbcDaConstrucao\AutorizacaoCliente\Http\Middleware\AclMiddleware;
use AbcDaConstrucao\AutorizacaoCliente\Services\AclService;
use AbcDaConstrucao\AutorizacaoCliente\Services\HttpClientService;
use AbcDaConstrucao\AutorizacaoCliente\Services\JWTService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AutorizacaoServiceProvider extends ServiceProvider
{
	/**
	 * Boot the authentication services for the application.
	 *
	 * @return void
	 */
	public function boot()
	{
		$path = __DIR__ . '/../../config/autorizacao_abc.php';
		$this->mergeConfigFrom($path, 'autorizacao_abc');
		$this->publishes([$path => $this->app->configPath('autorizacao_abc.php')], 'autorizacao_abc:config');
		$this->registerAuthGuard();
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

	protected function registerAuthGuard()
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

			return new AbcGenericUser($user);
		});
	}

	protected function registerAclMiddleware()
	{
		if ($this->isLumen()) {
			$this->app->routeMiddleware(['acl' => AclMiddleware::class]);
		} else {
			$router = $this->app['router'];

			if (method_exists($router, 'aliasMiddleware')) {
				$router->aliasMiddleware('acl', AclMiddleware::class);
			}

			if (method_exists($router, 'middleware')) {
				$router->middleware('acl', AclMiddleware::class);
			}
		}
	}

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
		/*try {
			foreach (ACL::getMapRoutes() as $route) {
				if (!empty($route->name)) {
					Gate::define($route->name, function ($user) use ($route) {
						$apps = $user->apps;
						return true;
					});
				}
			}
		} catch (\Exception $e) {
			Log::error($e->getMessage());
		}*/
	}

	protected function isLumen()
	{
		return class_exists('Laravel\Lumen\Application');
	}
}
