<?php

namespace AbcDaConstrucao\AutorizacaoCliente\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class JWTService
{
    /**
     * @return mixed
     */
    public function getToken()
    {
        return Cache::get(Config::get('autorizacao.cache.token'));
    }

    /**
     * @return mixed
     */
    public function getTokenTipo()
    {
        return Cache::get(Config::get('autorizacao.cache.token_tipo'));
    }

    /**
     * @return mixed
     */
    public function getTokenValidade()
    {
        return Cache::get(Config::get('autorizacao.cache.token_validade'));
    }


    public function getPayload($token = null)
    {
        $token = $token ?? $this->getToken();

        if (empty($token)) {
            return null;
        }

        $splitToken = explode('.', $token);

        if (!is_array($splitToken) || (is_array($splitToken) && count($splitToken) != 3)) {
            return null;
        }

        return json_decode(base64_decode($splitToken[1]), true);
    }

    public function getUser()
    {
        $payload = $this->getPayload();

        return $payload['user'] ?? null;
    }
}