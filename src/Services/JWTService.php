<?php

namespace AbcDaConstrucao\AutorizacaoCliente\Services;

use AbcDaConstrucao\AutorizacaoCliente\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class JWTService
{
    /**
     * @return mixed
     */
    public function getToken()
    {
        return Cache::get(Config::get('autorizacao_abc.cache.token'));
    }

    /**
     * @return mixed
     */
    public function getTokenType()
    {
        return Cache::get(Config::get('autorizacao_abc.cache.token_tipo'));
    }

    /**
     * @return mixed
     */
    public function getTokenValidade()
    {
        return Cache::get(Config::get('autorizacao_abc.cache.token_validade'));
    }

    /**
     * @param string|null $tokenTipo
     * @param string|null $token
     * @return bool
     */
    public function validate(string $tokenTipo = null, string $token = null)
    {
        $tokenTipo = $tokenTipo ?? $this->getTokenType();
        $token = $token ?? $this->getToken();

		if (empty($tokenTipo) || empty($token)) {
			return false;
		}

        return Http::validateTokenRequest($tokenTipo, $token);
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
