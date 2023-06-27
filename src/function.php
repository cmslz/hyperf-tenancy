<?php
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/11 23:34
 */

use Cmslz\HyperfTenancy\Kernel\Tenant\Tenant;
use Hyperf\Cache\Driver\DriverInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Paginator\AbstractPaginator;
use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Redis\RedisProxy;
use Hyperf\Resource\Json\JsonResource;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

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
     * @return mixed|ContainerInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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

if (!function_exists('tenant_cache')) {
    /**
     * 租户缓存
     * @return DriverInterface
     * Created by xiaobai at 2023/6/13 16:21
     */
    function tenant_cache(): DriverInterface
    {
        return tenancy()->cache();
    }
}


if (!function_exists('tenant_redis')) {
    /**
     * 中央域通用redis
     * @return RedisProxy
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * Created by xiaobai at 2023/2/15 14:21
     */
    function tenant_redis(): RedisProxy
    {
        return tenancy()->redis();
    }
}