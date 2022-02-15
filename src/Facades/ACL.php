<?php

namespace AbcDaConstrucao\AutorizacaoCliente\Facades;

use AbcDaConstrucao\AutorizacaoCliente\Services\AclService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \AbcDaConstrucao\AutorizacaoCliente\Services\AclService getMapRoutes()
 * @method static \AbcDaConstrucao\AutorizacaoCliente\Services\AclService normalizeRouteByFacade($route)
 * @method static \AbcDaConstrucao\AutorizacaoCliente\Services\AclService normalizeRouteByRequest($route)
 * @method static \AbcDaConstrucao\AutorizacaoCliente\Services\AclService routeMethodsToString(array $methods)
 * @method static \AbcDaConstrucao\AutorizacaoCliente\Services\AclService syncRoutes()
 * @method static \AbcDaConstrucao\AutorizacaoCliente\Services\AclService validate(object $mapRoute, $user)
 * @method static \AbcDaConstrucao\AutorizacaoCliente\Services\AclService appIsActive()
 *
 * @see AbcDaConstrucao\AutorizacaoCliente\Services\AclService
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
