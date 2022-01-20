<?php

namespace AbcDaConstrucao\AutorizacaoCliente\Services;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\App;
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
    public function getRoutesCollection()
    {
        $map = [];
        $appKey = Config::get('app.key');

        foreach (RouteFacade::getRoutes() as $index => $route) {
            /** @var Route $route */

            if ($route->action['prefix'] == '_ignition') {
                continue;
            }

            $map[$index] = [
                'methods' => implode(' | ', $route->methods),
                'route' => $route->uri,
                'name' => $route->getName(),
                'app_key' => $appKey,
            ];

            if (in_array('acl', $route->action['middleware']) ||
                in_array('auth', $route->action['middleware'])) {
                $map[$index]['public'] = false;
            } else {
                $map[$index]['public'] = true;
            }
        }

        return $map;
    }

    /** @return void */
    public function syncPermissions()
    {
        //$rotasMapeadas = PermissionAclModel::listAll();
        //$routeCollection = $this->getRoutesCollection();
        //self::removerNaoUsadas($rotasMapeadas, $routeCollection);
        //self::inserirNovas($rotasMapeadas, $routeCollection);
    }

    public function methodsToString(array $methods)
    {
        return implode(' | ', $methods);
    }

    /*public function validate(string $methods, string $route, int $userId)
    {
        $userGroups = User::getGroups($userId);

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

        return false;
    }*/

    /*public function validatePermissionMenu(string $routeName, int $userId)
    {
        $router = RouteFacade::getRoutes()->getByName($routeName);
        $methods = self::methodsToString($router->methods);
        $route = $router->uri;

        return self::validate($methods, $route, $userId);
    }*/

    /*private function removerNaoUsadas(array $rotasMapeadas, array $routeCollection)
    {
        $removerNaoUsadas = [];

        foreach ($rotasMapeadas as $rm) {
            $check = true;

            foreach ($routeCollection as $rc) {
                if (($rc['methods'] == $rm->methods) && ($rc['route'] == $rm->route)) {
                    $check = false;
                }
            }

            if ($check) {
                $removerNaoUsadas[] = $rm->id;
            }
        }

        if (!empty($removerNaoUsadas)) {
            PermissionAclModel::remove($removerNaoUsadas);
        }
    }*/

    /*private function inserirNovas(array $rotasMapeadas, array $routeCollection)
    {
        $novas = [];

        foreach ($routeCollection as $rc) {
            $check = true;

            foreach ($rotasMapeadas as $rm) {
                if (($rc['methods'] == $rm->methods) && ($rc['route'] == $rm->route)) {
                    $check = false;
                }
            }

            if ($check) {
                $novas[] = $rc;
            }
        }

        if (!empty($novas)) {
            PermissionAclModel::insert($novas);
        }
    }*/
}