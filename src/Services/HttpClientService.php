<?php

namespace AbcDaConstrucao\AutenticacaoPackage\Services;

use AbcDaConstrucao\AutenticacaoPackage\Facades\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class HttpClientService
{
    protected $hasSession;
    protected $config;
    protected Client $guzzle;

    public function __construct(bool $hasSession = false)
    {
        $this->hasSession = $hasSession;
        $this->config = Config::get('sag');
        $this->setGuzzle();
    }

    /**
     * @return void
     */
    protected function setGuzzle()
    {
        if ('/' != substr($this->config['base_url'], -1)) {
            $this->config['base_url'] = $this->config['base_url'] . '/';
        }

        $this->guzzle = new Client([
            'base_uri' => $this->config['base_url'],
            'connect_timeout' => $this->config['connect_timeout'],
            'timeout' => $this->config['timeout'],
            'verify' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * @param \Exception $e
     * @return array
     */
    protected function errorHandler(\Exception $e)
    {
        $status = ($e->getCode() >= 100 && $e->getCode() <= 511) ? $e->getCode() : 500;
        $data = $e->getMessage();

        if ($e instanceof RequestException) {
            $status = $e->getResponse()->getStatusCode();
            $data = json_decode($e->getResponse()->getBody()->getContents(), true)
                ?? $e->getResponse()->getBody()->getContents();
        }

        return [
            'status' => $status,
            'data' => $data
        ];
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loginRequest(string $username, string $password)
    {
        try {
            $resp = $this->guzzle->request('POST', 'auth/login', [
                'json' => [
                    'username' => $username,
                    'password' => $password,
                ],
            ]);

            $body = json_decode($resp->getBody()->getContents(), true) ?? $resp->getBody()->getContents();

            if ($this->hasSession && isset($body['token'])) {
                Session::put($this->config['session']['token_type'], $body['token_tipo']);
                Session::put($this->config['session']['token_validate'], $body['token_validade']);
                Session::put($this->config['session']['token'], $body['token']);
            } elseif($this->hasSession && !isset($body['token'])) {
                JWT::forgetToken();
            }

            return [
                'status' => $resp->getStatusCode(),
                'data' => $body
            ];
        } catch (\Exception $e) {
            return $this->errorHandler($e);
        }
    }

    /**
     * @param string|null $tokenTipo
     * @param string|null $token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function refreshTokenRequest(string $tokenTipo = null, string $token = null)
    {
        try {
            $tokenTipo = $tokenTipo ?? JWT::getTokenType();
            $token = $token ?? JWT::getToken();

            if (empty($tokenTipo) || empty($token)) {
                return false;
            }

            $resp = $this->guzzle->request('POST', 'auth/refresh', [
                'headers' => [
                    'Authorization' => "{$tokenTipo} {$token}",
                ],
            ]);

            $body = json_decode($resp->getBody()->getContents(), true) ?? $resp->getBody()->getContents();

            if ($this->hasSession && isset($body['token'])) {
                Session::put($this->config['session']['token_type'], $body['token_tipo']);
                Session::put($this->config['session']['token_validate'], $body['token_validade']);
                Session::put($this->config['session']['token'], $body['token']);
            } elseif($this->hasSession && !isset($body['token'])) {
                JWT::forgetToken();
            }

            return [
                'status' => $resp->getStatusCode(),
                'data' => $body
            ];
        } catch (\Exception $e) {
            return $this->errorHandler($e);
        }
    }

    /**
     * @param string|null $tokenTipo
     * @param string|null $token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function logoutRequest(string $tokenTipo = null, string $token = null)
    {
        try {
            $tokenTipo = $tokenTipo ?? JWT::getTokenType();
            $token = $token ?? JWT::getToken();

            if (empty($tokenTipo) || empty($token)) {
                return [
                    'status' => 409,
                    'data' => 'Token inválido'
                ];
            }

            $resp = $this->guzzle->request('POST', 'auth/logout', [
                'headers' => [
                    'Authorization' => "{$tokenTipo} {$token}",
                ],
            ]);

            if ($this->hasSession && in_array($resp->getStatusCode(), [200, 201])) {
                JWT::forgetToken();
            }

            return [
                'status' => $resp->getStatusCode(),
                'data' => json_decode($resp->getBody()->getContents(), true) ?? $resp->getBody()->getContents()
            ];
        } catch (\Exception $e) {
            return $this->errorHandler($e);
        }
    }

    /**
     * @param string|null $tokenTipo
     * @param string|null $token
     * @return array|false|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function userValidateRequest(string $tokenTipo = null, string $token = null)
    {
        try {
            $tokenTipo = $tokenTipo ?? JWT::getTokenType();
            $token = $token ?? JWT::getToken();

            if (empty($tokenTipo) || empty($token)) {
                return false;
            }

            $resp = $this->guzzle->request('POST', 'auth/validate', [
                'headers' => [
                    'Authorization' => "{$tokenTipo} {$token}",
                ],
            ]);

            return [
                'status' => $resp->getStatusCode(),
                'data' => json_decode($resp->getBody()->getContents(), true) ?? $resp->getBody()->getContents()
            ];
        } catch (\Exception $e) {
            return $this->errorHandler($e);
        }
    }

    /**
     * @param string|null $tokenTipo
     * @param string|null $token
     * @param array $data
     * @return array|false|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updatePasswordRequest(string $tokenTipo = null, string $token = null, array $data)
    {
        try {
            $tokenTipo = $tokenTipo ?? JWT::getTokenType();
            $token = $token ?? JWT::getToken();

            if (empty($tokenTipo) || empty($token)) {
                return false;
            }

            $resp = $this->guzzle->request('PUT', 'auth/password/update', [
                'headers' => [
                    'Authorization' => "{$tokenTipo} {$token}",
                ],
                'json' => $data
            ]);

            return [
                'status' => $resp->getStatusCode(),
                'data' => json_decode($resp->getBody()->getContents(), true) ?? $resp->getBody()->getContents()
            ];
        } catch (\Exception $e) {
            return $this->errorHandler($e);
        }
    }

    /**
     * @param array $data
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function syncRoutes(array $data)
    {
        try {
            $appName = $this->config['app_name'];
            $resp = $this->guzzle->request('POST', "apps/{$appName}/sync-routes", [
                'json' => $data,
                'headers' => [
                    'App-Key' => $this->config['app_key']
                ],
            ]);

            return [
                'status' => $resp->getStatusCode(),
                'data' => json_decode($resp->getBody()->getContents(), true) ?? $resp->getBody()->getContents()
            ];
        } catch (\Exception $e) {
            return $this->errorHandler($e);
        }
    }

    /**
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function appIsActive()
    {
        try {
            $appName = $this->config['app_name'];
            $resp = $this->guzzle->request('POST', "apps/{$appName}/is-active", [
                'headers' => [
                    'App-Key' => $this->config['app_key']
                ],
            ]);

            return [
                'status' => $resp->getStatusCode(),
                'data' => json_decode($resp->getBody()->getContents(), true) ?? $resp->getBody()->getContents()
            ];
        } catch (\Exception $e) {
            return $this->errorHandler($e);
        }
    }
}
