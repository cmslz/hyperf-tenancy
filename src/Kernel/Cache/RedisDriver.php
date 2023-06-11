<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/11 23:25
 */

namespace Cmslz\HyperfTenancy\Kernel\Cache;

use Psr\Container\ContainerInterface;

class RedisDriver extends \Hyperf\Cache\Driver\RedisDriver
{
    public function __construct(ContainerInterface $container, array $config)
    {
        $config['prefix'] .= 'central:';
        parent::__construct($container, $config);
    }
}