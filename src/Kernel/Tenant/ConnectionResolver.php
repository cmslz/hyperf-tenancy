<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/11 23:50
 */

namespace Cmslz\HyperfTenancy\Kernel\Tenant;

use Cmslz\HyperfTenancy\Kernel\Tenancy;
use Hyperf\Database\ConnectionInterface;

class ConnectionResolver extends \Hyperf\DbConnection\ConnectionResolver
{
    /**
     * All the registered connections.
     */
    protected array $connections = [];

    /**
     * Get a database connection instance.
     * @param null $name
     * @return ConnectionInterface
     * @throws \Cmslz\HyperfTenancy\Kernel\Exceptions\TenancyException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function connection($name = null): ConnectionInterface
    {
        return parent::connection(Tenancy::initDbConnectionName($name));
    }
}