<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/11 23:49
 */

namespace Cmslz\HyperfTenancy\Kernel\Tenant;

use Cmslz\HyperfTenancy\Kernel\Tenancy;

trait CentralConnection
{
    public function getConnectionName()
    {
        return Tenancy::getCentralConnection();
    }
}