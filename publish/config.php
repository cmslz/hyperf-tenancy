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
        'central_connection' => env('DB_CONNECTION', 'central'), // 不允许设置default
        'tenant_prefix' => 'tenant_', // 租户数据库前缀
        'base_database' => 'base',
        'max_connections' => env('TENANT_MAX_CONNECTIONS', 10), // 租户最大连接数
        'console_max_connections' => env('TENANT_CONSOLE_MAX_CONNECTIONS', 2), // 租户控制台最大连接数
    ],
    'cache' => [
        'tenant_prefix' => 'tenant_',
        'tenant_connection' => 'tenant',
        'central_connection' => 'central'
    ]
];