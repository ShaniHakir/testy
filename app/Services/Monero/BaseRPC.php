<?php

namespace App\Services\Monero;

use Illuminate\Support\Facades\Http;
use Exception;

abstract class BaseRPC
{
    protected $host;
    protected $port;
    protected $endpoint;

    public function __construct(array $config)
    {
        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->endpoint = "http://{$this->host}:{$this->port}/json_rpc";
    }

    public function request(string $method, array $params = [])
    {
        try {
            $response = Http::timeout(30)->post($this->endpoint, [
                'jsonrpc' => '2.0',
                'id' => '0',
                'method' => $method,
                'params' => $params
            ]);

            if ($response->failed()) {
                throw new Exception("RPC request failed: " . $response->body());
            }

            $result = $response->json();

            if (isset($result['error'])) {
                throw new Exception("RPC error: " . $result['error']['message']);
            }

            if (!isset($result['result'])) {
                throw new Exception("Invalid RPC response: missing result");
            }

            return $result['result'];
        } catch (Exception $e) {
            \Log::error('RPC request failed', [
                'method' => $method,
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
