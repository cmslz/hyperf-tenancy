<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/11 23:44
 */

namespace Cmslz\HyperfTenancy\Kernel\Tenant\Cache;


use Psr\Container\ContainerInterface;

class RedisDriver extends \Hyperf\Cache\Driver\RedisDriver
{
    public function __construct(ContainerInterface $container, array $config)
    {
        $config['prefix'] .= tenancy()->getId() . ':';
        parent::__construct($container, $config);
    }
}