<?php
declare(strict_types=1);

/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/7/6 10:03
 */

namespace Cmslz\HyperfTenancy\Kernel\Amqp\AsyncQueue\Jobs;


use Cmslz\HyperfTenancy\Kernel\Exceptions\TenancyException;
use Exception;
use Hyperf\AsyncQueue\Job;
use Hyperf\Amqp\Message\ProducerMessage;
use Hyperf\Context\ApplicationContext;
use Hyperf\Amqp\Producer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 延迟队列
 * Class DelayMqJob
 * @package Cmslz\HyperfTenancy\Kernel\Amqp\AsyncQueue\Jobs
 */
class DelayMqJob extends Job
{
    public mixed $params;

    public string $producerClassName;

    /**
     * 任务执行失败后的重试次数，即最大执行次数为 $maxAttempts+1 次
     */
    protected int $maxAttempts = 2;

    /**
     * 设置重试次数
     * @param int $maxAttempts
     * Created by xiaobai at 2023/7/6 10:11
     */
    public function setMaxAttempts(int $maxAttempts): static
    {
        $this->maxAttempts = $maxAttempts;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function __construct(string $producerClassName, $params)
    {
        if (!class_exists($producerClassName)) {
            throw new TenancyException(sprintf('%s class no exist', $producerClassName));
        }
        $producerClass = new $producerClassName($params);
        if (!$producerClass instanceof ProducerMessage) {
            throw new TenancyException(sprintf('%s class example not ProducerMessage', $producerClassName));
        }
        $this->params = $params;
        $this->producerClassName = $producerClassName;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * Created by xiaobai at 2023/7/6 10:13
     */
    public function handle()
    {
        $className = $this->producerClassName;
        $class = new $className($this->params);
        if (function_exists('newQueue')) {
            newQueue($class);
        } else {
            $producer = ApplicationContext::getContainer()->get(Producer::class);
            $producer->produce($class);
        }
    }
}