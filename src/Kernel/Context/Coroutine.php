<?php
declare(strict_types=1);

namespace Cmslz\HyperfTenancy\Kernel\Context;

use Cmslz\HyperfTenancy\Kernel\Exceptions\TenancyException;
use Cmslz\HyperfTenancy\Kernel\Log\AppendRequestIdProcessor;
use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Coroutine as Co;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class Coroutine
{
    protected LoggerInterface $logger;

    public function __construct(protected ContainerInterface $container)
    {
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    /**
     * @param callable $callable
     * @return int Returns the coroutine ID of the coroutine just created.
     * Returns -1 when coroutine create failed.
     * @throws TenancyException
     */
    public function create(callable $callable): int
    {
        $id = \Hyperf\Coroutine\Coroutine::id();
        $tenantId = tenancy()->getId(false);
        $coroutine = Co::create(
            function () use ($callable, $id, $tenantId) {
                try {
                    if ($tenantId) {
                        tenancy()->init($tenantId);
                    }
                    // Shouldn't copy all contexts to avoid socket already been bound to another coroutine.
//                    Context::copy(
//                        $id,
//                        [
//                            AppendRequestIdProcessor::REQUEST_ID,
//                            ServerRequestInterface::class,
//                        ]
//                    );
                    call($callable);
                } catch (Throwable $throwable) {
                    $this->logger->warning((string)$throwable);
                }
            }
        );

        try {
            return $coroutine->getId();
        } catch (\Throwable $throwable) {
            $this->logger->warning((string)$throwable);
            return -1;
        }
    }
}
