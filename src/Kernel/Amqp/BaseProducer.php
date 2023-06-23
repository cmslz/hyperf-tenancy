<?php
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/24 1:21
 */

namespace Cmslz\HyperfTenancy\Kernel\Amqp;


use Hyperf\Amqp\Message\ProducerMessage;
use Hyperf\Amqp\Packer\Packer;
use Hyperf\Context\ApplicationContext;

class BaseProducer extends ProducerMessage
{
    public function serialize(): string
    {
        $packer = ApplicationContext::getContainer()->get(Packer::class);
        $this->payload = json_encode(['payload' => $this->payload, 'tenant_id' => tenancy()->getId(false)]);
        return $packer->pack($this->payload);
    }
}