<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/7/6 9:45
 */

namespace Cmslz\HyperfTenancy\Kernel\Amqp\AsyncQueue;

use Hyperf\AsyncQueue\JobInterface;
use Hyperf\AsyncQueue\JobMessage;
use Hyperf\Contract\UnCompressInterface;

class AsyncMessage extends JobMessage
{
    /**
     * @var int
     */
    public $id;

    public function __construct(JobInterface $job)
    {
        parent::__construct($job);
        if (empty($this->id)) {
            $this->id = tenancy()->getId(false);
        }
    }

    public function __serialize(): array
    {
        return [
            $this->job,
            $this->attempts,
            $this->id,
        ];
    }

    public function __unserialize($serialized): void
    {
        [$job, $attempts, $id] = $serialized;
        if ($job instanceof UnCompressInterface) {
            $job = $job->uncompress();
        }
        $this->job = $job;
        $this->attempts = $attempts;
        $this->id = $id;
    }
}