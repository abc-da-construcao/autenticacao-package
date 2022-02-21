<?php

namespace AbcDaConstrucao\AutenticacaoPackage\Facades;

use AbcDaConstrucao\AutenticacaoPackage\Services\HttpClientService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\HttpClientService loginRequest(string $username, string $password)
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\HttpClientService logoutRequest(string $tokenTipo = null, string $token = null)
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\HttpClientService userValidateRequest(string $tokenTipo, string $token)
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\HttpClientService updatePasswordRequest(string $tokenTipo, string $token, array $data)
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\HttpClientService syncRoutes(array $data)
 * @method static \AbcDaConstrucao\AutenticacaoPackage\Services\HttpClientService appIsActive()
 *
 * @see AbcDaConstrucao\AutenticacaoPackage\Services\HttpClientService
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
