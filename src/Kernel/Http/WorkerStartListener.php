<?php
declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/11 23:40
 */

namespace Cmslz\HyperfTenancy\Kernel\Http;


use Hyperf\Framework\Logger\StdoutLogger;
use Hyperf\Server\Listener\AfterWorkerStartListener;
use Psr\Container\ContainerInterface;

class WorkerStartListener extends AfterWorkerStartListener
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container->get(StdoutLogger::class));
    }
}
