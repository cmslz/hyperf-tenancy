<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/11 23:49
 */

namespace Cmslz\HyperfTenancy\Kernel\Resource;


use Cmslz\HyperfTenancy\Kernel\Resource\Src\AnonymousResourceCollection;
use Hyperf\Resource\Json\JsonResource;

class BaseResource extends JsonResource
{
    public static function collection($resource)
    {
        if (!is_paginator($resource)) {
            return parent::collection($resource);
        }
        return tap(
            new AnonymousResourceCollection($resource, static::class),
            function ($collection) {
                $collection->preserveKeys = (new static([]))->preserveKeys;
            }
        );
    }
}