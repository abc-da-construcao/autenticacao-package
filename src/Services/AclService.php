<?php

namespace AbcDaConstrucao\AutorizacaoCliente\Services;

use AbcDaConstrucao\AutorizacaoCliente\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
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
            $route = $this->normalizeRouteByFacade($route);
            $map[$index] = (object)[
                'method' => $this->routeMethodsToString($route->methods),
                'uri' => $route->uri,
                'name' => $route->name,
            ];

            if (
                in_array('acl', $route->action['middleware']) ||
                in_array('auth', $route->action['middleware']) ||
                in_array('auth:web', $route->action['middleware']) ||
                in_array('auth:api', $route->action['middleware'])
            ) {
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
     * @return \Illuminate\Routing\Route|object
     */
    public function normalizeRouteByRequest(Request $request)
    {
        $route = $request->route();

        if (is_array($route)) {
            $route = (object)$route[1];
            $route->uri = $request->path();

            if ('/' != substr($route->uri, 0, 1)) {
                $route->uri = '/' . $route->uri;
            }

            $route->name = $route->as ?? null;
            $route->methods = [$request->method()];
            $route->action['middleware'] = $route->middleware ?? [];
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

    /**
     * @param array $methods
     * @return string
     */
    public function routeMethodsToString(array $methods)
    {
        return implode('|', $methods);
    }

    /**
     * @param string $currentRouteMethod
     * @param string $currentRouteUri
     * @param $user
     * @return bool
     */
    public function validate(string $currentRouteMethod, string $currentRouteUri, $user)
    {
        $appName = Config::get('abc_autorizacao.app_name');
        $app = collect($user->apps)->firstWhere('name', $appName);

        if (empty($app)) {
            return false;
        }

        if ($app['super_admin'] == 1) {
            return true;
        }

        foreach ($app['grupos'] as $grupo) {
            foreach ($grupo['permissoes'] as $route) {
                if ($currentRouteMethod == $route['method'] && $currentRouteUri == $route['uri']) {
                    return true;
                }
            }
        }

        return false;
    }
}
