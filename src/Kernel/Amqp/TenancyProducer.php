<?php
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/24 1:21
 */

namespace Cmslz\HyperfTenancy\Kernel\Amqp;


use Cmslz\HyperfTenancy\Kernel\Amqp\AsyncQueue\Jobs\DelayMqJob;
use Hyperf\Amqp\Message\ProducerMessage;
use Hyperf\Amqp\Packer\Packer;
use Hyperf\Context\ApplicationContext;
use Hyperf\AsyncQueue\Driver\DriverFactory;

abstract class TenancyProducer extends ProducerMessage
{
    public function serialize(): string
    {
        $packer = ApplicationContext::getContainer()->get(Packer::class);
        $this->payload = json_encode(['payload' => $this->payload, 'tenant_id' => tenancy()->getId(false)]);
        return $packer->pack($this->payload);
    }

    /**
     * 设置延迟时间
     * @param int $delay
     * @param string $key
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * Created by xiaobai at 2023/7/6 10:38
     */
    public function delay(int $delay, string $key = 'default'): bool
    {
        $driver = di()->get(DriverFactory::class)->get($key);
        return $driver->push(new DelayMqJob(static::class, ...$this->payload), $delay);
    }
}