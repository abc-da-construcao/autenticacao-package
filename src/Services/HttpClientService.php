<?php

namespace AbcDaConstrucao\AutorizacaoCliente\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class HttpClientService
{
    protected Client $guzzle;

    public function __construct()
    {
        $this->setGuzzle();
    }

    /**
     * @return void
     */
    protected function setGuzzle()
    {
        $url = Config::get('autorizacao.base_url');

        if ('/' != substr($url, -1)) {
            $url = $url . '/';
        }

        $this->guzzle = new Client([
            'base_uri' => $url,
            'connect_timeout' => Config::get('autorizacao.connect_timeout'),
            'timeout' => Config::get('autorizacao.timeout'),
            'verify' => false,
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
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
        $contents = $e->getMessage();

        if ($e instanceof RequestException) {
            $contents = json_decode($e->getResponse()->getBody()->getContents(), true);
        }

        return [
            'success' => 'false',
            'data' => $contents,
        ];
    }

    /**
     * @param string $username
     * @param string $password
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function login(string $username, string $password)
    {
        try {
            $resp = $this->guzzle->request('POST', 'api/auth/login', [
                'json' => [
                    'username' => $username,
                    'password' => $password,
                ],
            ]);

            $body = json_decode($resp->getBody()->getContents(), true);

            Cache::add(Config::get('autorizacao.cache.token_tipo'), $body['token_tipo']);
            Cache::add(Config::get('autorizacao.cache.token_validade'), $body['token_validade']);
            Cache::add(Config::get('autorizacao.cache.token'), $body['token']);

            return [
                'success' => 'true',
                'data' => $body
            ];
        } catch (\Exception $e) {
            return $this->errorHandler($e);
        }
    }

    public function tokenValidate(string $tokenTipo, string $token)
    {
        try {
            $resp = $this->guzzle->request('POST', 'api/user/check', [
                'headers' => [
                    'Authorization' => "{$tokenTipo} {$token}"
                ],
            ]);

            if (in_array($resp->getStatusCode(), [200, 201])) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
