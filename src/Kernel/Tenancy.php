<?php
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/30 10:07
 */

namespace Cmslz\HyperfTenancy\Kernel;


use Cmslz\HyperfTenancy\Kernel\Exceptions\TenancyException;
use Cmslz\HyperfTenancy\Kernel\Tenant\Cache\CacheManager;
use Cmslz\HyperfTenancy\Kernel\Tenant\Models\Domain;
use Cmslz\HyperfTenancy\Kernel\Tenant\Models\Tenants as TenantModel;
use Cmslz\HyperfTenancy\Kernel\Tenant\Tenant;
use Exception;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Database\ConnectionInterface;
use Hyperf\DbConnection\Db;
use Hyperf\DbConnection\Pool\PoolFactory;
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use phpseclib3\File\ASN1\Maps\IssuerAltName;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Redis;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

class Tenancy
{
    /**
     * @throws TenancyException
     */
    public static function tenantModel(): TenantModel
    {
        $class = config('tenancy.tenant_model');
        $tenantModel = new $class();
        if (!$tenantModel instanceof TenantModel) {
            throw new TenancyException('tenant_model instanceof error!');
        }
        return $tenantModel;
    }

    /**
     * @return Domain
     * @throws TenancyException
     * Created by xiaobai at 2023/6/13 15:51
     */
    public static function domainModel(): Domain
    {
        $class = config('tenancy.domain_model');
        $domainModel = new $class();

        if (!$domainModel instanceof Domain) {
            throw new TenancyException('domain_model instanceof error!');
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws TenancyException
     */
    public static function runForMultiple($tenants, callable $callable)
    {
        // Convert null to all tenants
        $tenants = empty($tenants) ? self::tenantModel()::query()->distinct()->orderBy('created_at')->pluck('id')->toArray() : $tenants;

        // Convert incrementing int ids to strings
        $tenants = is_int($tenants) ? (string)$tenants : $tenants;

        // Wrap string in array
        $tenants = is_string($tenants) ? [$tenants] : $tenants;
        $originalTenantId = tenancy()->getId(false);
        try {
            foreach ($tenants as $tenantId) {
                call($callable, [tenancy()->init($tenantId)]);
            }
        } catch (Exception $exception) {
            throw $exception;
        } finally {
            tenancy()->init($originalTenantId, false);
        }
    }

    /**
     * 租户通用Redis
     * @return RedisProxy
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws TenancyException
     * Created by xiaobai at 2023/2/14 18:47
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
     * @throws TenancyException
     */
    public static function tenancyDatabase(string $id = null)
    {
        if (empty($id)) {
            $id = tenancy()->getId();
        }
        return self::getTenantDbPrefix() . $id;
    }

    /**
     * 获取租户最大连接数
     * @return int
     */
    public static function getTenantMaxConnections(): int
    {
        $tenantMaxConnections = config_base()->get('tenancy.database.max_connections', 0);
        if (!self::checkIfHttpRequest() && config_base()->has('tenancy.database.console_max_connections')) {
            $tenantConsoleMaxConnections = config_base()->get('tenancy.database.console_max_connections');
            if (!empty($tenantConsoleMaxConnections)) {
                $tenantMaxConnections = $tenantConsoleMaxConnections;
            }
        }
        return intval($tenantMaxConnections);
    }

    /**
     * @param string|null $name
     * @return string|null
     * @throws TenancyException
     * Created by xiaobai at 2023/8/3 13:46
     */
    public static function initDbConnectionName(string $name = null): ?string
    {
        if (empty($name) && !empty(tenancy()->getId(false))) {
            $name = Tenancy::getTenantDbPrefix();
        }
        if ($name === self::getTenantDbPrefix()) {
            $id = tenancy()->getId();
            $name = self::tenancyDatabase();
            $key = 'databases.' . self::getCentralConnection();

            if (empty(config_base()->has($key))) {
                throw new TenancyException(sprintf('config[%s] is not exist!', $key));
            }
            $tenantDatabaseConfig = config_base()->get($key);
            $tenantDatabaseConfig["database"] = $name;
            if (isset($tenantDatabaseConfig['cache']['prefix'])) {
                $tenantDatabaseConfig['cache']['prefix'] .= $id;
            }
            $tenantMaxConnections = self::getTenantMaxConnections();
            if (!empty($tenantMaxConnections) && isset($tenantDatabaseConfig['pool']['max_connections'])) {
                $tenantDatabaseConfig['pool']['max_connections'] = $tenantMaxConnections;
            }
            config_base()->set("databases." . $name, $tenantDatabaseConfig);
        }
        return $name;
    }

    public static function checkIfHttpRequest(): bool
    {
        $request = Context::get(ServerRequestInterface::class);
        if ($request !== null) {
            // 存在 HTTP 请求
            return true;
        }
        // 不存在 HTTP 请求
        return false;
    }
}