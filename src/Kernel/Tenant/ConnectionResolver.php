<?php

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
     *
     * @param null $name
     * @return ConnectionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function connection($name = null): ConnectionInterface
    {
        if (!empty(tenancy()->getId(false)) && !in_array($name, Tenancy::extendConnections())) {
            $name = Tenancy::initDbConnectionName();
        }
        return parent::connection($name);
    }
}