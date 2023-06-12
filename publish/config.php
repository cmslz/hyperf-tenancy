<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/12 18:26
 */
return [
    'tenant_model' => \Cmslz\HyperfTenancy\Kernel\Tenant\Models\Tenants::class,
    'domain_model' => \Cmslz\HyperfTenancy\Kernel\Tenant\Models\Domain::class,
    'central_domains' => [
        '127.0.0.1',
        'localhost'
    ],
    'database' => [
        'connection' => env('DB_CONNECTION', 'central'),
        'prefix' => 'tenant',
    ],
    'cache' => [
        'tag_base' => 'tenant'
    ],
    'redis' => [
        'prefix' => 'tenant',
        'connection' => 'tenant'
    ]
];