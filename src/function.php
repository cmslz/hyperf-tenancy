<?php
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/11 23:34
 */

use Cmslz\HyperfTenancy\Kernel\Tenant\Tenant;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Paginator\AbstractPaginator;
use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Resource\Json\JsonResource;

if (!function_exists('call')) {
    function call($callback, array $args = [])
    {
        return \Hyperf\Support\call($callback, $args);
    }
}

if (!function_exists('config_base')) {
    function config_base(): ConfigInterface
    {
        return ApplicationContext::getContainer()->get(ConfigInterface::class);
    }
}

if (!function_exists('di')) {
    /**
     * Finds an entry of the container by its identifier and returns it.
     * @param string|null $id
     * @return mixed|\Psr\Container\ContainerInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function di(?string $id = null)
    {
        $container = ApplicationContext::getContainer();
        if ($id) {
            return $container->get($id);
        }

        return $container;
    }
}
if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return \Hyperf\Config\config($key, $default);
    }
}

if (!function_exists('env')) {
    function env($key, $default = null): mixed
    {
        return \Hyperf\Support\env($key, $default);
    }
}

if (!function_exists('is_paginator')) {
    function is_paginator(mixed $data): bool
    {
        return $data instanceof LengthAwarePaginator || $data instanceof LengthAwarePaginatorInterface || $data instanceof AbstractPaginator;
    }
}

if (!function_exists('resource_format_data')) {
    /**
     * 格式化结构体数据
     * @param mixed $data
     * @return mixed
     * Created by xiaobai at 2023/2/25 16:57
     */
    function resource_format_data(mixed $data): mixed
    {
        if (is_paginator($data) || ($data instanceof JsonResource && is_paginator($data->resource))) {
            return [
                'data' => $data->items(),
                'current_page' => $data->currentPage(),
                'total' => $data->total()
            ];
        }
        return $data;
    }
}

if (!function_exists('tenancy')) {
    function tenancy(): Tenant
    {
        return Tenant::instance();
    }
}

if (!function_exists('tenant_go')) {
    function tenant_go(callable $callable)
    {
        $id = tenancy()->getId();
        Swoole\Coroutine::create(
            function () use ($id, $callable) {
                tenancy()->init($id);
                call($callable);
            }
        );
    }
}