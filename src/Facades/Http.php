<?php

namespace AbcDaConstrucao\AutorizacaoCliente\Facades;

use AbcDaConstrucao\AutorizacaoCliente\Services\HttpClientService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \AbcDaConstrucao\AutorizacaoCliente\Services\HttpClientService login()
 *
 * @see AbcDaConstrucao\AutorizacaoCliente\Services\HttpClientService
 */
class Http extends Facade
{
/**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return HttpClientService::class;
    }
}
