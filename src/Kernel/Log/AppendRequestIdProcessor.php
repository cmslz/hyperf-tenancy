<?php
declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/11 23:08
 */

namespace Cmslz\HyperfTenancy\Kernel\Log;


use Hyperf\Context\Context;
use Hyperf\Coroutine\Coroutine;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class AppendRequestIdProcessor implements ProcessorInterface
{
    public const REQUEST_ID = 'log.request.id';

    public function __invoke(LogRecord $record)
    {
        $record->context['request_id'] = Context::getOrSet(self::REQUEST_ID, uniqid());
        $record->context['coroutine_id'] = Coroutine::id();
        return $record;
    }
}
