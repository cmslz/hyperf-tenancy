<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/11 23:50
 */

namespace Cmslz\HyperfTenancy\Kernel\Tenant;

use Cmslz\HyperfTenancy\Kernel\Exceptions\TenancyException;
use Hyperf\Context\Context;
use Cmslz\HyperfTenancy\Kernel\Tenancy;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Coroutine\Coroutine;

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
     * @throws TenancyException
     */
    public function connection($name = null): ConnectionInterface
    {
        $name = Tenancy::initDbConnectionName($name);
        $connection = null;
        if (Context::has($name)) {
            $connection = Context::get($name);
        }
        if (!$connection instanceof ConnectionInterface) {
            $pool = $this->factory->getPool($name);
            $connection = $pool->get();
            try {
                $connection = $connection->getConnection();
                Context::set($name, $connection);
            } finally {
                if (Coroutine::inCoroutine()) {
                    defer(function () use ($connection) {
                        $connection->release();
                    });
                }
            }
        }
        return $connection;
    }
}