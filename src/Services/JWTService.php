<?php

namespace AbcDaConstrucao\AutenticacaoPackage\Services;

use AbcDaConstrucao\AutenticacaoPackage\Facades\Http;
use Illuminate\Support\Facades\Config;

class JWTService
{
    protected $hasSessionFacade;

    public function __construct()
    {
        $this->hasSessionFacade = class_exists('Illuminate\Support\Facades\Session')
            && !class_exists('Laravel\Lumen\Application');
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        if ($this->hasSessionFacade) {
            return session()->get(Config::get('auth_abc.session.token'));
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getTokenType()
    {
        if ($this->hasSessionFacade) {
            return session()->get(Config::get('auth_abc.session.token_type'));
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getTokenValidade()
    {
        if ($this->hasSessionFacade) {
            return session()->get(Config::get('auth_abc.session.token_validate'));
        }

        return null;
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
}
