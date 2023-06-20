<?php
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/11 23:34
 */

use Cmslz\HyperfTenancy\Kernel\Tenant\Tenant;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\Cache\CacheManager;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Paginator\AbstractPaginator;
use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Hyperf\Resource\Json\JsonResource;
use Psr\Container\ContainerExceptionInterface;
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

if (!function_exists('cache')) {
    /**
     * 中央域通用缓存
     * @return \Hyperf\Cache\Driver\DriverInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * Created by xiaobai at 2023/6/13 16:21
     */
    function cache()
    {
        $centralConnection = config('tenancy.cache.central_connection', 'central');
        return ApplicationContext::getContainer()->get(CacheManager::class)->getDriver($centralConnection);
    }
}


if (!function_exists('redis')) {
    /**
     * 中央域通用redis
     * @return RedisProxy
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * Created by xiaobai at 2023/2/15 14:21
     */
    function redis(): RedisProxy
    {
        $centralConnection = config('tenancy.cache.central_connection', 'central');
        $redis = ApplicationContext::getContainer()->get(RedisFactory::class)->get($centralConnection);
        $redis->setOption(Redis::OPT_PREFIX, $centralConnection . ':');
        return $redis;
    }
}

if (!function_exists('tenant_cache')) {
    /**
     * 租户缓存
     * @return \Hyperf\Cache\Driver\DriverInterface
     * Created by xiaobai at 2023/6/13 16:21
     */
    function tenant_cache(): \Hyperf\Cache\Driver\DriverInterface
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

if (!function_exists('tenant_queue_push')) {
    /**
     * 租户队列
     * Push a job to async queue.
     */
    function tenant_queue_push(JobInterface $job, int $delay = 0): bool
    {
        $driver = di()->get(DriverFactory::class)->get(config('tenancy.async_queue.tenant_connection', 'tenant'));
        return $driver->push($job, $delay);
    }
}

if (!function_exists('queue_push')) {
    /**
     * 中央域队列
     * Push a job to async queue.
     */
    function queue_push(JobInterface $job, int $delay = 0): bool
    {
        $driver = di()->get(DriverFactory::class)->get(config('tenancy.async_queue.central_connection', 'central'));
        return $driver->push($job, $delay);
    }
}