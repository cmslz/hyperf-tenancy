<?php
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/30 10:07
 */

namespace Cmslz\HyperfTenancy\Kernel;


use Cmslz\HyperfTenancy\Kernel\Tenant\Cache\CacheManager;
use Cmslz\HyperfTenancy\Kernel\Tenant\Models\Domain;
use Cmslz\HyperfTenancy\Kernel\Tenant\Models\Tenants as TenantModel;
use Cmslz\HyperfTenancy\Kernel\Tenant\Tenant;
use Hyperf\Context\ApplicationContext;
use Hyperf\Redis\RedisFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Tenancy
{
    /**
     * @throws Exception
     */
    public static function tenantModel(): TenantModel
    {
        $class = config('tenancy.tenant_model');
        $tenantModel = new $class();
        if (!$tenantModel instanceof TenantModel) {
            throw new Exception('tenant_model instanceof error!');
        }
        return $tenantModel;
    }

    /**
     * @return Domain
     * @throws Exception
     * Created by xiaobai at 2023/6/13 15:51
     */
    public static function domainModel(): Domain
    {
        $class = config('tenancy.domain_model');
        $domainModel = new $class();

        if (!$domainModel instanceof Domain) {
            throw new Exception('domain_model instanceof error!');
        }
        return $domainModel;
    }

    /**
     * 中央域数据库链接池
     * @return string
     * Created by xiaobai at 2023/6/13 15:43
     */
    public static function getCentralConnection(): string
    {
        return config('tenancy.database.central_connection', 'central');
    }

    /**
     * 租户数据库前缀
     * @return string
     * Created by xiaobai at 2023/6/13 15:43
     */
    public static function getTenantDbPrefix(): string
    {
        return config('tenancy.database.tenant_prefix', 'tenant_');
    }

    /**
     * 指定租户内执行
     * @param $tenants
     * @param callable $callable
     * @throws Exception
     * Created by xiaobai at 2023/2/14 14:02
     */
    public static function runForMultiple($tenants, callable $callable)
    {
        // Convert null to all tenants
        $tenants = empty($tenants) ? self::tenantModel()::query()->distinct()->orderBy('created_at')->pluck('id')->toArray() : $tenants;

        // Convert incrementing int ids to strings
        $tenants = is_int($tenants) ? (string)$tenants : $tenants;

        // Wrap string in array
        $tenants = is_string($tenants) ? [$tenants] : $tenants;
        $originalTenantId = Tenant::instance()->getId(false);
        try {
            foreach ($tenants as $tenantId) {
                call($callable, [tenancy()->init($tenantId)]);
                tenancy()->init($originalTenantId, false);
            }
        } catch (Exception $exception) {
            throw $exception;
        } finally {
            tenancy()->init($originalTenantId, false);
        }
    }

    /**
     * 租户通用Redis
     * @return \Hyperf\Redis\RedisProxy
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface Created by xiaobai at 2023/2/14 18:47
     */
    public static function redis()
    {
        $redis = ApplicationContext::getContainer()->get(RedisFactory::class)->get(config('tenancy.cache.tenant_connection'));
        $redis->setOption(Redis::OPT_PREFIX,
            config('tenancy.cache.tenant_prefix', 'tenant_') . Tenant::instance()->getId());
        return $redis;
    }

    /**
     * 缓存
     * Created by xiaobai at 2023/2/14 18:50
     */
    public static function cache()
    {
        $tenantKey = config('tenancy.cache.tenant_prefix', 'tenant_') . Tenant::instance()->getId();
        return ApplicationContext::getContainer()->get(CacheManager::class)->setTenantConfig($tenantKey)->getDriver($tenantKey);
    }

    /**
     * 初始数据库
     * @return mixed
     * Created by xiaobai at 2023/6/30 10:47
     */
    public static function baseDatabase()
    {
        return config('tenancy.database.base_database');
    }

    /**
     * 获取当前租户数据库
     * @param string|null $id
     * @return string
     * Created by xiaobai at 2023/6/30 10:06
     */
    public static function tenancyDatabase(string $id = null)
    {
        if (empty($id)) {
            $id = tenancy()->getId();
        }
        return self::getTenantDbPrefix() . $id;
    }

    public static function initDbConnectionName(string $name = null): ?string
    {
        if ($name === self::getTenantDbPrefix()) {
            $id = tenancy()->getId();
            $name = self::tenancyDatabase();
            $key = 'databases.' . self::getCentralConnection();

            if (empty(config_base()->has($key))) {
                throw new InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
            }
            $tenantDatabaseConfig = config_base()->get($key);
            $tenantDatabaseConfig["database"] = $name;
            if (isset($tenantDatabaseConfig['cache']['prefix'])) {
                $tenantDatabaseConfig['cache']['prefix'] .= $id;
            }
            config_base()->set("databases." . $name, $tenantDatabaseConfig);
        }
        return $name;
    }
}