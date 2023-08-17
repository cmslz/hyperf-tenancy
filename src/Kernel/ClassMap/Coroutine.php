<?php
declare(strict_types=1);

namespace Hyperf\Coroutine;

use Cmslz\HyperfTenancy\Kernel\Context\Coroutine as Go;
use Hyperf\Engine\Coroutine as Co;
use Hyperf\Engine\Exception\CoroutineDestroyedException;
use Hyperf\Engine\Exception\RunningInNonCoroutineException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Coroutine
{
    /**
     * Returns the current coroutine ID.
     * Returns -1 when running in non-coroutine context.
     */
    public static function id(): int
    {
        return Co::id();
    }

    public static function defer(callable $callable)
    {
        Co::defer($callable);
    }

    public static function sleep(float $seconds)
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

    /**
     * @param callable $callable
     * @return int Returns the coroutine ID of the coroutine just created.
     *             Returns -1 when coroutine create failed.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function create(callable $callable): int
    {
        return di()->get(Go::class)->create($callable);
    }

    public static function inCoroutine(): bool
    {
        return Co::id() > 0;
    }
}