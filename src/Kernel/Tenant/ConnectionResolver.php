<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/11 23:50
 */

namespace Cmslz\HyperfTenancy\Kernel\Tenant;


use InvalidArgumentException;
use Hyperf\Database\ConnectionInterface;

class ConnectionResolver extends \Hyperf\DbConnection\ConnectionResolver
{
    /**
     * All the registered connections.
     */
    protected array $connections = [];

    /**
     * Get a database connection instance.
     *
     * @param null $name
     * @return ConnectionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function connection($name = null): ConnectionInterface
    {
        if ($name !== 'central') {
            $id = tenancy()->getId();
            $name = 'tenant_' . $id;
            $key = 'databases.default';

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
        return parent::connection($name);
    }
}