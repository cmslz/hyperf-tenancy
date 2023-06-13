<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/11 23:43
 */

namespace Cmslz\HyperfTenancy\Kernel\Tenant\Cache;


class CacheManager extends \Hyperf\Cache\CacheManager
{
    public function setTenantConfig($configKey): static
    {
        if (!$this->config->has($configKey)) {
            $config = $this->config->get('cache.' . $this->config->get('tenancy.cache.tenant_connection', 'tenant'));
            // 每个tenant后缀不一样
            $config['prefix'] .= tenancy()->getId() . ':';
            $this->config->set('cache.' . $configKey, $config);
        }
        return $this;
    }
}