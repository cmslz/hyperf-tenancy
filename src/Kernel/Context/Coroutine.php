<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/11 23:29
 */

namespace Cmslz\HyperfTenancy\Kernel\Context;

use Cmslz\HyperfTenancy\Kernel\Log\AppendRequestIdProcessor;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Coroutine as Co;
use Hyperf\Engine\Exception\CoroutineDestroyedException;
use Hyperf\Engine\Exception\RunningInNonCoroutineException;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
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

    public static function id(): int
    {
        return Co::id();
    }

    public static function defer(callable $callable): void
    {
        Co::defer(static function () use ($callable) {
            try {
                $callable();
            } catch (Throwable $throwable) {
                static::printLog($throwable);
            }
        });
    }

    public static function sleep(float $seconds): void
    {
        usleep(intval($seconds * 1000 * 1000));
    }

    /**
     * Returns the parent coroutine ID.
     * Returns 0 when running in the top level coroutine.
     * @throws RunningInNonCoroutineException when running in non-coroutine context
     * @throws CoroutineDestroyedException when the coroutine has been destroyed
     */
    public static function parentId(?int $coroutineId = null): int
    {
        return Co::pid($coroutineId);
    }

    public static function inCoroutine(): bool
    {
        return Co::id() > 0;
    }

    public static function stats(): array
    {
        return Co::stats();
    }

    public static function exists(int $id): bool
    {
        return Co::exists($id);
    }

    private static function printLog(Throwable $throwable): void
    {
        if (ApplicationContext::hasContainer()) {
            $container = ApplicationContext::getContainer();
            if ($container->has(StdoutLoggerInterface::class)) {
                $logger = $container->get(StdoutLoggerInterface::class);
                if ($container->has(FormatterInterface::class)) {
                    $formatter = $container->get(FormatterInterface::class);
                    $logger->warning($formatter->format($throwable));
                } else {
                    $logger->warning((string)$throwable);
                }
            }
        }
    }

    /**
     * @return int Returns the coroutine ID of the coroutine just created.
     * Returns -1 when coroutine create failed.
     */
    public function create(callable $callable): int
    {
        $id = \Hyperf\Coroutine\Coroutine::id();
        $tenantId = tenancy()->getId(false);
        $coroutine = Co::create(
            function () use ($callable, $id, $tenantId) {
                if (!empty($tenantId)) {
                    tenancy()->init($tenantId);
                }
                try {
                    // Shouldn't copy all contexts to avoid socket already been bound to another coroutine.
                    Context::copy(
                        $id,
                        [
                            AppendRequestIdProcessor::REQUEST_ID,
                            ServerRequestInterface::class,
                        ]
                    );
                    call($callable);
                } catch (Throwable $throwable) {
                    $this->logger->warning((string)$throwable);
                }
            }
        );

        try {
            return $coroutine->getId();
        } catch (Throwable $throwable) {
            $this->logger->warning((string)$throwable);
            return -1;
        }
    }
}