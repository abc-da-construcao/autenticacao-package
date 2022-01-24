<?php

namespace AbcDaConstrucao\AutorizacaoCliente\Services;

use AbcDaConstrucao\AutorizacaoCliente\Facades\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class HttpClientService
{
	protected $config;
	protected Client $guzzle;

	public function __construct()
	{
		$this->config = Config::get('autorizacao_abc');
		$this->setGuzzle();
	}

	/**
	 * @return void
	 */
	protected function setGuzzle()
	{
		$url = $this->config['base_url'];

		if ('/' == substr($url, -1)) {
			$url = substr($url, 0, -1);
		}

		$this->guzzle = new Client([
			'base_uri' => $url,
			'connect_timeout' => $this->config['connect_timeout'],
			'timeout' => $this->config['timeout'],
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

		return $contents;
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function loginRequest(string $username, string $password)
	{
		try {
			$resp = $this->guzzle->request('POST', '/api/auth/login', [
				'json' => [
					'username' => $username,
					'password' => $password,
				],
			]);

			$body = json_decode($resp->getBody()->getContents(), true);

			if ($this->config['cache']['ativo'] == true) {
				Cache::put($this->config['cache']['token_tipo'], (string)$body['token_tipo'], 600);
				Cache::put($this->config['cache']['token_validade'], (string)$body['token_validade'], 600);
				Cache::put($this->config['cache']['token'], (string)$body['token'], 600);
			} elseif ($this->config['cache']['ativo'] == false && Cache::has($this->config['cache']['token'])) {
				Cache::forget($this->config['cache']['token_tipo']);
				Cache::forget($this->config['cache']['token_validade']);
				Cache::forget($this->config['cache']['token']);
			}

			return $body;
		} catch (\Exception $e) {
			return $this->errorHandler($e);
		}
	}

	/**
	 * @param string|null $tokenTipo
	 * @param string|null $token
	 * @return bool
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function logoutRequest(string $tokenTipo = null, string $token = null)
	{
		try {
			$tokenTipo = $tokenTipo ?? JWT::getTokenType();
			$token = $token ?? JWT::getToken();

			if (empty($tokenTipo) || empty($token)) {
				return false;
			}

			$resp = $this->guzzle->request('POST', '/api/auth/logout', [
				'headers' => [
					'Authorization' => "{$tokenTipo} {$token}",
				],
			]);

			if (in_array($resp->getStatusCode(), [200, 201])) {
				if (Cache::has($this->config['cache']['token'])) {
					Cache::forget($this->config['cache']['token_tipo']);
					Cache::forget($this->config['cache']['token_validade']);
					Cache::forget($this->config['cache']['token']);
				}

				return true;
			}

			return false;
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * @param string $tokenTipo
	 * @param string $token
	 * @return array|false|mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getUserRequest(string $tokenTipo, string $token)
	{
		try {
			$tokenTipo = $tokenTipo ?? JWT::getTokenType();
			$token = $token ?? JWT::getToken();

			if (empty($tokenTipo) || empty($token)) {
				return false;
			}

			$resp = $this->guzzle->request('POST', '/api/auth/user', [
				'headers' => [
					'Authorization' => "{$tokenTipo} {$token}",
				],
			]);

			return json_decode($resp->getBody()->getContents(), true);
		} catch (\Exception $e) {
			return $this->errorHandler($e);
		}
	}

	public function syncRoutes(array $data)
	{
		try {
			$resp = $this->guzzle->request('POST', '/api/app/sync-routes', [
				'json' => $data,
				'headers' => [
					'key' => Config::get('autorizacao_abc.app_key'),
					'id' => Config::get('autorizacao_abc.app_id'),
				],
			]);

			return json_decode($resp->getBody()->getContents(), true);
		} catch (\Exception $e) {
			return $this->errorHandler($e);
		}
	}
}
