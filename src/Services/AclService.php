<?php

namespace AbcDaConstrucao\AutorizacaoCliente\Services;

use AbcDaConstrucao\AutorizacaoCliente\Facades\Http;
use Illuminate\Support\Facades\Route as RouteFacade;

class AclService
{
	/**
	 * Busca todas as rotas da aplicação atual e faz uma map de quais são publicas ou protegidas
	 * para enviar/sincronizar com a API de Autenticação/Autorização.
	 *
	 * @return array
	 */
	public function getMapRoutes()
	{
		$index = 0;
		$map = [];

		foreach (RouteFacade::getRoutes() as $route) {
			$route = $this->normalizeRoute($route);

			if (!empty($route->action['prefix']) && $route->action['prefix'] == '_ignition') {
				continue;
			}

			$map[$index] = (object)[
				'method' => $this->methodsToString($route->methods),
				'uri' => $route->uri,
				'name' => $route->name,
			];

			if (in_array('acl', $route->action['middleware']) ||
				in_array('auth', $route->action['middleware']) ||
				in_array('auth:web', $route->action['middleware']) ||
				in_array('auth:api', $route->action['middleware'])) {
				$map[$index]->public = false;
			} else {
				$map[$index]->public = true;
			}

			$index++;
		}

		return $map;
	}

	/**
	 * Normaliza as diferenças de chaves da classe Route do Laravel e Lumen
	 *
	 * @param mixed $route
	 * @return \Illuminate\Routing\Route|object
	 */
	protected function normalizeRoute($route)
	{
		if (is_array($route)) {
			$route = (object)$route;
			$route->name = $route->action['as'] ?? null;
			$route->methods = [$route->method];
			$route->action['middleware'] = $route->action['middleware'] ?? [];
		} else {
			$route->name = $route->getName();

			if ('/' != substr($route->uri, 0, 1)) {
				$route->uri = '/' . $route->uri;
			}
		}

		return $route;
	}

	/**
	 * @return \AbcDaConstrucao\AutorizacaoCliente\Services\HttpClientService
	 */
	public function syncRoutes()
	{
		$data = [
			'routes' => $this->getMapRoutes(),
		];

		return Http::syncRoutes($data);
	}

	public function methodsToString(array $methods)
	{
		return implode('|', $methods);
	}

	public function validate(array $currentRouteMethods, string $currentRouteUri)
	{
		/*$userGroups = User::getGroups($userId);

		if (empty($userGroups)) {
			return false;
		}

		foreach ($userGroups as $group) {
			if ($group->id == 1) {
				return true; // usuário administrador pode tudo!
			}

			$group->permissions = PermissionAclModel::getPermissionsGroup($group->id);

			foreach ($group->permissions as $permission) {
				if ($methods == $permission->methods && $route == $permission->route) {
					return true; // rota existe em um dos grupos do usuário.
				}
			}
		}

		return false;*/
	}

	/*public function validatePermissionMenu(string $routeName, int $userId)
	{
		$router = RouteFacade::getRoutes()->getByName($routeName);
		$methods = self::methodsToString($router->methods);
		$route = $router->uri;

		return self::validate($methods, $route, $userId);
	}*/
}
