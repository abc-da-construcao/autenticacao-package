<?php

namespace AbcDaConstrucao\AutorizacaoCliente\Facades;

use AbcDaConstrucao\AutorizacaoCliente\Services\JWTService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \AbcDaConstrucao\AutorizacaoCliente\Services\JWTService getToken()
 * @method static \AbcDaConstrucao\AutorizacaoCliente\Services\JWTService getTokenTipo()
 * @method static \AbcDaConstrucao\AutorizacaoCliente\Services\JWTService getTokenValidade()
 * @method static \AbcDaConstrucao\AutorizacaoCliente\Services\JWTService getPayload($token = null)
 * @method static \AbcDaConstrucao\AutorizacaoCliente\Services\JWTService getUser($token = null)
 * @method static \AbcDaConstrucao\AutorizacaoCliente\Services\JWTService validate(string $tokenTipo = null, string $token = null)
 *
 * @see AbcDaConstrucao\AutorizacaoCliente\Services\JWTService
 */
class JWT extends Facade
{
/**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return JWTService::class;
    }
}
