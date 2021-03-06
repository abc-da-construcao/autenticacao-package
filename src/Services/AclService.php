<?php

namespace AbcDaConstrucao\AutenticacaoPackage\Services;

use AbcDaConstrucao\AutenticacaoPackage\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use stdClass;

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
        $routes = [];

        if (class_exists('Laravel\Lumen\Application')) {
            $routes = Route::getRoutes();
        } else {
            $routes = Route::getRoutes()->getRoutes();
        }

        foreach ($routes as $route) {
            $route = $this->normalizeRouteByFacade($route);
            $map[$index] = (object)[
                'method' => $this->routeMethodsToString($route->methods),
                'uri' => $route->uri,
                'name' => $route->name,
            ];
            $authMiddlewareArray = preg_grep("/auth/", $route->action['middleware']);
            $map[$index]->public = count($authMiddlewareArray) === 0;
            $map[$index]->guard = null;

            if (!empty($authMiddlewareArray)) {
                $array = explode(':', $authMiddlewareArray[array_key_first($authMiddlewareArray)]);
                $map[$index]->guard = $array[1] ?? Config::get("auth.defaults.guard");
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
    public function normalizeRouteByFacade($route)
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
     * @param \Illuminate\Http\Request $request
     * @return object|null
     */
    public function normalizeRouteByRequest(Request $request)
    {
        $routeNormalized = new stdClass;
        $route = $request->route();

        if (empty($route)) {
            return null;
        } elseif (is_array($route)) {
            $routeNormalized->uri = $request->getPathInfo();

            if ('/' != substr($routeNormalized->uri, 0, 1)) {
                $routeNormalized->uri = '/' . $routeNormalized->uri;
            }

            $routeNormalized->name = $route[1]['as'] ?? null;
            $routeNormalized->methods = [$request->getMethod()];
            $routeNormalized->action['middleware'] = $route[1]['middleware'] ?? [];
        } else {
            $routeNormalized->uri = $route->uri;

            if ('/' != substr($routeNormalized->uri, 0, 1)) {
                $routeNormalized->uri = '/' . $routeNormalized->uri;
            }

            $routeNormalized->name = $route->getName();
            $routeNormalized->methods = $route->methods;
            $routeNormalized->action = $route->action ?? [];
        }

        return $routeNormalized;
    }

    /**
     * @param string $currentUri
     * @param string $mappedUri
     * @return boolean
     */
    public function compareUriElements(string $currentUri, string $mappedUri)
    {
        if ('/' == substr($currentUri, -1)) {
            $currentUri = substr($currentUri, 0,-1);
        }

        if ('/' == substr($mappedUri, -1)) {
            $mappedUri = substr($mappedUri, 0,-1);
        }

        $currentUriArray = explode('/', $currentUri);
        $mappedUriArray = explode('/', $mappedUri);

        if (count($currentUriArray) != count($mappedUriArray)) {
            return false;
        }

        $uriElementsDifferent = array_diff($mappedUriArray,$currentUriArray);
        $resultCompare = true;

        foreach ($uriElementsDifferent as $uriElement) {
            $resultCompare = strstr($uriElement, '{');
        }

        return $resultCompare;
    }

    /**
     * @return \AbcDaConstrucao\AutenticacaoPackage\Services\HttpClientService
     */
    public function syncRoutes()
    {
        $data = [
            'routes' => $this->getMapRoutes(),
        ];

        return Http::syncRoutes($data);
    }

    /**
     * @param array $methods
     * @return string
     */
    public function routeMethodsToString(array $methods)
    {
        return implode('|', $methods);
    }

    /**
     * @param object $mapRoute
     * @param $user
     * @return bool
     */
    public function validate(object $mapRoute, $user)
    {
        if (!empty($user->active) && $user->active == 0) {
            return false;
        }

        $appName = Config::get('sag.app_name');
        $app = collect($user->apps)->firstWhere('name', $appName);

        if (empty($app) || $app['active'] == 0) {
            return false;
        }

        if ($mapRoute->public) {
            return true;
        }

        if ($app['super_admin'] == 1) {
            return true;
        }

        foreach ($app['groups'] as $group) {
            if ($group['active'] == 1) {
                foreach ($group['permissions'] as $route) {
                    if ($mapRoute->method == $route['method'] &&
                        $this->compareUriElements($mapRoute->uri, $route['uri'])
                    ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function appIsActive()
    {
        $resp = Http::appIsActive();

        if (isset($resp['status']) && $resp['status'] == 200 && $resp['data'] == true) {
            return true;
        }

        return false;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function isAuthSagJwtDriver(Request $request)
    {
        $route = self::normalizeRouteByRequest($request);
        $authMiddlewareArray = preg_grep("/auth/", $route->action['middleware']);

        if (empty($authMiddlewareArray)) {
            return false;
        }

        $array = explode(':', $authMiddlewareArray[array_key_first($authMiddlewareArray)]);

        if (count($array) == 2) {
            return 'sag-jwt' === Config::get("auth.guards.{$array[1]}.driver");
        } elseif (count($array) == 1) {
            $guard = Config::get("auth.defaults.guard");

            return 'sag-jwt' === Config::get("auth.guards.{$guard}.driver");
        }

        return false;
    }

    /**
     * @param Request $request
     * @return string|null
     */
    public function getGuard(Request $request)
    {
        $route = self::normalizeRouteByRequest($request);

        if (empty($route)) {
            return null;
        }

        $authMiddlewareArray = preg_grep("/auth/", $route->action['middleware']);

        if (empty($authMiddlewareArray)) {
            return null;
        }

        $array = explode(':', $authMiddlewareArray[array_key_first($authMiddlewareArray)]);

        if (count($array) == 2) {
            return $array[1];
        } elseif (count($array) == 1) {
            return Config::get("auth.defaults.guard");
        }

        return null;
    }

    /**
     * @param string $routeNameOrUri
     * @param string $guard
     * @return bool
     */
    public function hasRouteAccess(string $routeNameOrUri, string $guard = 'web')
    {
        $user = Auth::guard('sag')->user();
        $route = null;

        foreach ($this->getMapRoutes() as $mapRoute) {
            if ($mapRoute->name == $routeNameOrUri || $this->compareUriElements($mapRoute->uri, $routeNameOrUri)) {
                $route = $mapRoute;
            }
        };

        if (empty($user) || empty($route)) {
            return false;
        }

        return $this->validate($route, $user);
    }
}
