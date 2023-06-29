<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/13 17:06
 */

namespace Cmslz\HyperfTenancy\Concerns;


use Symfony\Component\Console\Input\InputOption;

trait HasATenantsOption
{
    protected function getOptions(): array
    {
        $options = parent::getOptions();
        array_push($options, ['tenants', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, '', null]);
        foreach ($options as &$option) {
            // 设置默认链接项为中央域链接
            if ($option[0] === 'database' && empty($option[1])) {
                $option[1] = tenancy()->getCentralConnection();
            }
        }
        return $options;
    }

    /**
     * Prepare the migration database for running.
     */
    protected function prepareDatabase()
    {
        $this->migrator->setConnection(tenancy()->getTenantDbPrefix() . tenancy()->getId());

        if (!$this->migrator->repositoryExists()) {
            $this->call('migrate:install', array_filter([
                '--database' => $this->input->getOption('database'),
            ]));
        }
    }

    protected function getTenants()
    {
        return tenancy()->tenantModel()::query()
            ->when($this->input->getOption('tenants'), function ($query) {
                $query->whereIn(tenancy()->tenantModel()->primaryKey, $this->input->getOption('tenants'));
            })
            ->cursor();
    }
}
