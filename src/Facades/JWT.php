<?php

namespace AbcDaConstrucao\AutenticacaoPackage\Facades;

use AbcDaConstrucao\AutenticacaoPackage\Services\JWTService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\JWTService getToken()
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\JWTService getTokenType()
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\JWTService getTokenValidade()
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\JWTService forgetToken()
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\JWTService getPayload($token = null)
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\JWTService validate(string $tokenTipo = null, string $token = null)
 *
 * @see AbcDaConstrucao\AutenticacaoPackage\Services\JWTService
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
