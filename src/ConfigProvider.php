<?php
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/12 23:21
 */

namespace Cmslz\HyperfTenancy;


class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
                Commands\Install::class,
                Commands\Migrate::class,
            ],
            'listeners'=>[
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                    'class_map' => [
                        Hyperf\Coroutine\Coroutine::class => BASE_PATH . '/src/Kernel/ClassMap/Coroutine.php',
                        Hyperf\Di\Resolver\ResolverDispatcher::class => BASE_PATH . '/src/Kernel/ClassMap/ResolverDispatcher.php',
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