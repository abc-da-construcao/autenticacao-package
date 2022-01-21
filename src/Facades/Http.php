<?php

namespace AbcDaConstrucao\AutorizacaoCliente\Facades;

use AbcDaConstrucao\AutorizacaoCliente\Services\HttpClientService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \AbcDaConstrucao\AutorizacaoCliente\Services\HttpClientService loginRequest(string $username, string $password)
 * @method static \AbcDaConstrucao\AutorizacaoCliente\Services\HttpClientService logoutRequest(string $tokenTipo = null, string $token = null)
 * @method static \AbcDaConstrucao\AutorizacaoCliente\Services\HttpClientService validateTokenRequest(string $tokenTipo, string $token)
 * @method static \AbcDaConstrucao\AutorizacaoCliente\Services\HttpClientService syncRoutes(array $data)
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
