<?php
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/13 17:41
 */

namespace Cmslz\HyperfTenancy;


class TenancyConfigProvider
{

    public function __invoke(): array
    {
        return [
            'dependencies' => [
                \Hyperf\Contract\StdoutLoggerInterface::class => \Cmslz\HyperfTenancy\Kernel\Log\LoggerFactory::class,
                \Hyperf\Server\Listener\AfterWorkerStartListener::class => \Cmslz\HyperfTenancy\Kernel\Http\WorkerStartListener::class,
                \Psr\EventDispatcher\EventDispatcherInterface::class => \Cmslz\HyperfTenancy\Kernel\Event\EventDispatcherFactory::class,
                \Hyperf\Database\ConnectionResolverInterface::class => \Cmslz\HyperfTenancy\Kernel\Tenant\ConnectionResolver::class,
            ],
            'commands' => [
                ...[
                    Commands\Migrate::class,
                    Commands\Rollback::class,
                ]
            ],
            'listeners'=>[
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            // 组件默认配置文件，即执行命令后会把 source 的对应的文件复制为 destination 对应的的文件
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'description of this config file.', // 描述
                    // 建议默认配置放在 publish 文件夹中，文件命名和组件名称相同
                    'source' => __DIR__ . '/../publish/config.php',  // 对应的配置文件路径
                    'destination' => BASE_PATH . '/config/autoload/tenancy.php', // 复制为这个路径下的该文件
                ],

            ],
        ];
    }
}