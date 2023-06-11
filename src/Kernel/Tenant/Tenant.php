<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/12 0:02
 */

namespace Cmslz\HyperfTenancy\Kernel\Tenant;


use Cmslz\HyperfTenancy\Kernel\Tenant\Cache\CacheManager;
use Cmslz\HyperfTenancy\Kernel\Tenant\Models\Domain;
use Exception;
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Redis\RedisFactory;
use Hyperf\Support\Traits\StaticInstance;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Cmslz\HyperfTenancy\Kernel\Tenant\Models\Tenants as TenantModel;
use Redis;
use Swoole\Coroutine\Channel;

class Tenant
{

    use StaticInstance;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $id;

    protected TenantModel|null $tenant;

    public function __construct()
    {
        $this->container = ApplicationContext::getContainer();
    }

    public function init($id = null)
    {
        if (empty($id)) {
            $request = $this->container->get(RequestInterface::class);
            $id = $request->header('x-tenant-id');
            if (empty($id)) {
                $id = $request->query('tenant');
            }
            if (empty($id)) {
                $id = Domain::tenantIdByDomain($request->header('Host'));
            }
        }
        // 过滤根目录
        if (empty($id)) {
            throw new InvalidArgumentException('The tenant is invalid.');
        }

        /**
         * @var TenantModel $tenant
         */
        $tenant = TenantModel::query()->where('id', $id)->first();
        if (empty($tenant)) {
            throw new InvalidArgumentException(
                sprintf('The tenant %s is invalid', $id)
            );
        }
        $this->id = $id;
        $this->tenant = $tenant;
    }

    public function getId()
    {
        // 过滤根目录
        if (empty($this->id)) {
            throw new InvalidArgumentException('The tenant is invalid.');
        }
        return $this->id;
    }

    /**
     * 获取当前租户
     * @return TenantModel
     * Created by xiaobai at 2023/2/16 14:42
     */
    public function getTenant(): TenantModel
    {
        return $this->tenant;
    }

    /**
     * 制度租户内执行
     * @param $tenants
     * @param callable $callable
     * @throws Exception
     * Created by xiaobai at 2023/2/14 14:02
     */
    public function runForMultiple($tenants, callable $callable)
    {
        // Convert null to all tenants
        $tenants = empty($tenants) ? TenantModel::query()->distinct()->orderBy('created_at')->pluck('id')->toArray() : $tenants;

        // Convert incrementing int ids to strings
        $tenants = is_int($tenants) ? (string)$tenants : $tenants;

        // Wrap string in array
        $tenants = is_string($tenants) ? [$tenants] : $tenants;
        $originalTenantId = $this->getId();
        try {
            foreach ($tenants as $tenantId) {
                // 保证进程执行完毕后再执行下一个进程
                $channel = new Channel(1);
                tenancy()->init($tenantId);
                $callable = function () use ($callable, $channel) {
                    $result = call($callable);
                    $channel->push($result);
                    return $result;
                };
                tenant_go($callable);
                $channel->pop();
                tenancy()->init($originalTenantId);
            }
        } catch (Exception $exception) {
            throw $exception;
        } finally {
            $this->init($originalTenantId);
        }
    }

    /**
     * 租户通用Redis
     * @param string $poolName
     * @return \Hyperf\Redis\RedisProxy
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface Created by xiaobai at 2023/2/14 18:47
     */
    public function redis(string $poolName = 'tenant')
    {
        $redis = $this->container->get(RedisFactory::class)->get($poolName);
        $redis->setOption(Redis::OPT_PREFIX, 'tenant:' . $this->id);
        return $redis;
    }

    /**
     * 缓存
     * Created by xiaobai at 2023/2/14 18:50
     */
    public function cache()
    {
        $tenantKey = "tenant_" . $this->id;
        return $this->container->get(CacheManager::class)->setTenantConfig($tenantKey)->getDriver($tenantKey);
    }
}