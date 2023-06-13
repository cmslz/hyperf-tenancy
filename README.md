# hyperf-tenancy

https://github.com/cmslz/hyperf-tenancy

## 配置

- tenancy.php

> [config.tenancy](/publish/config.php)

- annotations.php 配置

> 创建 `Coroutine::class` 继承 `\Cmslz\HyperfTenancy\Kernel\ClassMap\Coroutine` 命名空间 `Hyperf\Coroutine`
> 
> 创建 `ResolverDispatcher::class` 继承 `\Cmslz\HyperfTenancy\Kernel\ClassMap\ResolverDispatcher` 命名空间 `Hyperf\Di\Resolver`

```PHP
return [
    'scan' => [
        ...[],
        'class_map' => [
            Hyperf\Coroutine\Coroutine::class => BASE_PATH . '/src/Kernel/ClassMap/Coroutine.php',
            Hyperf\Di\Resolver\ResolverDispatcher::class => BASE_PATH . '/src/Kernel/ClassMap/ResolverDispatcher.php',
        ],
    ],
];
```

- async_queue.php

> driver:Cmslz\HyperfTenancy\Kernel\Tenant\AsyncQueue\RedisDriver::class

- cache.php

```PHP
return [
    ...[],
    // 中央域缓存
    'central' => [
        'driver' => \Cmslz\HyperfTenancy\Kernel\Cache\RedisDriver::class,
        'packer' => Hyperf\Codec\Packer\PhpSerializerPacker::class,
        'prefix' => 'central:cache:',
    ],
    // 租户缓存
    'tenant' => [
        'driver' => \Cmslz\HyperfTenancy\Kernel\Tenant\Cache\RedisDriver::class,
        'packer' => Hyperf\Codec\Packer\PhpSerializerPacker::class,
        'prefix' => 'tenant:cache:'
    ],
];

```

- databases.php

```PHP
return [
    ...[],
    'central' => [
        'driver' => env('DB_DRIVER', 'mysql'),
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_CENTRAL_DATABASE', 'central'),
        'username' => env('DB_CENTRAL_USERNAME', 'root'),
        'password' => env('DB_CENTRAL_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
        'prefix' => env('DB_CENTRAL_PREFIX', ''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
        'cache' => [
            'handler' => Hyperf\ModelCache\Handler\RedisHandler::class,
            'cache_key' => '{mc:%s:m:%s}:%s:%s',
            'prefix' => 'central',
            'ttl' => 3600 * 24,
            'empty_model_ttl' => 600,
            'load_script' => true,
        ],
        'commands' => [
            'gen:model' => [
                'path' => 'app/Model',
                'force_casts' => true,
                'inheritance' => 'Model',
                'uses' => '',
                'refresh_fillable' => true,
                'table_mapping' => [],
            ],
        ],
    ]
];
```

- dependencies.php

```PHP
return [
    ...[],
    Hyperf\Contract\StdoutLoggerInterface::class => Cmslz\HyperfTenancy\Kernel\Log\LoggerFactory::class,
    Hyperf\Server\Listener\AfterWorkerStartListener::class => Cmslz\HyperfTenancy\Kernel\Http\WorkerStartListener::class,
    Psr\EventDispatcher\EventDispatcherInterface::class => Cmslz\HyperfTenancy\Kernel\Event\EventDispatcherFactory::class,
    Hyperf\Database\ConnectionResolverInterface::class => Cmslz\HyperfTenancy\Kernel\Tenant\ConnectionResolver::class,
];
```

- redis.php

```PHP
return [
    ...[],
    // 中央域通用redis
    'central' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', null),
        'port' => (int)env('REDIS_PORT', 6379),
        'db' => (int)env('REDIS_DB', 0),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 32,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float)env('REDIS_MAX_IDLE_TIME', 60),
        ]
    ],
    // 租户通用redis
    'tenant' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', null),
        'port' => (int)env('REDIS_PORT', 6379),
        'db' => (int)env('REDIS_DB', 0),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 32,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float)env('REDIS_MAX_IDLE_TIME', 60),
        ]
    ]
];
```

## 使用

    在需要使用路由添加中间件 `TenantMiddleware::class`
