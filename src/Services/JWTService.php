<?php

namespace AbcDaConstrucao\AutenticacaoPackage\Services;

use AbcDaConstrucao\AutenticacaoPackage\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class JWTService
{
    /**
     * @return mixed
     */
    public function getToken()
    {
        return Cache::get(Config::get('auth_abc.cache.token'));
    }

    /**
     * @return mixed
     */
    public function getTokenType()
    {
        return Cache::get(Config::get('auth_abc.cache.token_tipo'));
    }

    /**
     * @return mixed
     */
    public function getTokenValidade()
    {
        return Cache::get(Config::get('auth_abc.cache.token_validade'));
    }

    /**
     * @param string|null $tokenTipo
     * @param string|null $token
     * @return array|bool
     */
    public function validate(string $tokenTipo = null, string $token = null)
    {
        $tokenTipo = $tokenTipo ?? $this->getTokenType();
        $token = $token ?? $this->getToken();

        if (empty($tokenTipo) || empty($token)) {
            return false;
        }

        $resp = Http::userValidateRequest($tokenTipo, $token);

        if (!empty($resp['data']['id'])) {
            return $resp['data'];
        }

        return false;
    }

    /**
     * @param $token
     * @return mixed|null
     */
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

    /**
     * @return mixed|null
     */
    public function getUser($token = null)
    {
        $payload = $this->getPayload($token);

        return $payload['user'] ?? null;
    }
}
