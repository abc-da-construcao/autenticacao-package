<?php

namespace AbcDaConstrucao\AutorizacaoCliente\Facades;

use AbcDaConstrucao\AutorizacaoCliente\Services\AclService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \AbcDaConstrucao\AutorizacaoCliente\Services\AclService getRoutesCollection()
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
