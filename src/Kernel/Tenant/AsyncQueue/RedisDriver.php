<?php
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/12 0:04
 */

namespace Cmslz\HyperfTenancy\Kernel\Tenant\AsyncQueue;


use Cmslz\HyperfTenancy\Kernel\Tenant\AsyncMessage;
use Hyperf\AsyncQueue\JobInterface;

class RedisDriver extends \Hyperf\AsyncQueue\Driver\RedisDriver
{
    public function push(JobInterface $job, int $delay = 0): bool
    {
        $message = new AsyncMessage($job);
        $data = $this->packer->pack($message);

        if ($delay === 0) {
            return (bool)$this->redis->lPush($this->channel->getWaiting(), $data);
        }

        return $this->redis->zAdd($this->channel->getDelayed(), time() + $delay, $data) > 0;
    }

    public function delete(JobInterface $job): bool
    {
        $message = new AsyncMessage($job);
        $data = $this->packer->pack($message);

        return (bool)$this->redis->zRem($this->channel->getDelayed(), $data);
    }
}