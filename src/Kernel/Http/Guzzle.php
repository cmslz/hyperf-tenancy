<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/11 23:32
 */

namespace Cmslz\HyperfTenancy\Kernel\Http;


use GuzzleHttp\Client;

trait Guzzle
{
    private Client $client;
    public array $defaultConfig = [];
    public array $userConfig = [];

    private function mergeData(array $data)
    {
        return array_replace_recursive($this->defaultConfig, $this->userConfig, $data);
    }

    public function post(string $uri, array $data)
    {
        return $this->client->post($uri, $this->mergeData($data));
    }

    public function get(string $uri, array $data)
    {
        return $this->client->get($uri, $this->mergeData($data));
    }

    public function put(string $uri, array $data)
    {
        return $this->client->put($uri, $this->mergeData($data));
    }

    public function delete(string $uri, array $data)
    {
        return $this->client->delete($uri, $this->mergeData($data));
    }
}