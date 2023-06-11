<?php
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/3/1 18:10
 */

namespace Cmslz\HyperfTenancy\Kernel\Resource\Src;

use Psr\Http\Message\ResponseInterface;

class AnonymousResourceCollection extends \Hyperf\Resource\Json\AnonymousResourceCollection
{
    public function __construct($resource, $collects)
    {
        parent::__construct($resource, $collects);
    }

    public function additional(array $data)
    {
        $this->additional = array_merge($this->additional, $data);
        return $this;
    }

    protected function isPaginatorResource($resource): bool
    {
        return is_paginator($resource);
    }

    public function toResponse(): ResponseInterface
    {
        if ($this->isPaginatorResource($this->resource)) {
            return (new PaginatedResourceResponse($this))->toResponse();
        }

        return parent::toResponse();
    }
}