<?php
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/24 1:31
 */

namespace Cmslz\HyperfTenancy\Kernel\Amqp;


use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Packer\Packer;
use Hyperf\Context\ApplicationContext;

class BaseConsumer extends ConsumerMessage
{
    public function unserialize(string $data)
    {
        $container = ApplicationContext::getContainer();
        $packer = $container->get(Packer::class);
        $result = $packer->unpack($data);
        $body = json_decode($result, true);
        list('payload' => $payload, 'tenant_id' => $tenantId) = $body;
        if (!empty($tenantId)) {
            tenancy()->init($tenantId);
        }
        return $payload;
    }
}