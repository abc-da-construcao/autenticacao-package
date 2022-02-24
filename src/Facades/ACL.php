<?php

namespace AbcDaConstrucao\AutenticacaoPackage\Facades;

use AbcDaConstrucao\AutenticacaoPackage\Services\AclService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\AclService getMapRoutes()
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\AclService normalizeRouteByFacade($route)
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\AclService normalizeRouteByRequest($route)
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\AclService routeMethodsToString(array $methods)
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\AclService syncRoutes()
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\AclService validate(object $mapRoute, $user)
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\AclService appIsActive()
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\AclService hasRouteAccess(string $routeNameOrUri)
 *
 * @see AbcDaConstrucao\AutenticacaoPackage\Services\AclService
 */
class ACL extends Facade
{
/**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return AclService::class;
    }
}
