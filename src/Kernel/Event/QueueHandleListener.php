<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Cmslz\HyperfTenancy\Kernel\Event;

use Cmslz\HyperfTenancy\Kernel\Tenant\AsyncMessage;
use Hyperf\AsyncQueue\AnnotationJob;
use Hyperf\AsyncQueue\Event\AfterHandle;
use Hyperf\AsyncQueue\Event\BeforeHandle;
use Hyperf\AsyncQueue\Event\Event;
use Hyperf\AsyncQueue\Event\FailedHandle;
use Hyperf\AsyncQueue\Event\RetryHandle;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class QueueHandleListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            AfterHandle::class,
            BeforeHandle::class,
            FailedHandle::class,
            RetryHandle::class,
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof Event && $event->getMessage()->job()) {
            $job = $event->getMessage()->job();
            $jobClass = get_class($job);
            if ($job instanceof AnnotationJob) {
                $jobClass = sprintf('Job[%s@%s]', $job->class, $job->method);
            }
            $date = date('Y-m-d H:i:s');

            switch (true) {
                case $event instanceof BeforeHandle:
                    if ($event->getMessage() instanceof AsyncMessage) {
                        tenancy()->init($event->getMessage()->id);
                    }
                    logger()->info(sprintf('[%s] Processing %s.', $date, $jobClass));
                    break;
                case $event instanceof AfterHandle:
                    logger()->info(sprintf('[%s] Processed %s.', $date, $jobClass));
                    break;
                case $event instanceof FailedHandle:
                    logger()->error(sprintf('[%s] Failed %s.', $date, $jobClass));
                    logger()->error((string) $event->getThrowable());
                    break;
                case $event instanceof RetryHandle:
                    logger()->warning(sprintf('[%s] Retried %s.', $date, $jobClass));
                    break;
                default:
                    logger()->error(sprintf('[%s] Error %s.', $date, $jobClass));
            }
        }
    }
}
