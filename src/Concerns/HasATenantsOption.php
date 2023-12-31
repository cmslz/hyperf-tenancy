<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/13 17:06
 */

namespace Cmslz\HyperfTenancy\Concerns;


use Cmslz\HyperfTenancy\Kernel\Tenancy;
use Symfony\Component\Console\Input\InputOption;

trait HasATenantsOption
{
    protected function getOptions(): array
    {
        $options = parent::getOptions();
        array_push($options, ['tenants', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, '', null]);
        return $options;
    }

    protected function getTenants()
    {
        return Tenancy::tenantModel()::query()
            ->when($this->input->getOption('tenants'), function ($query) {
                $query->whereIn(Tenancy::tenantModel()->primaryKey, $this->input->getOption('tenants'));
            })
            ->cursor();
    }
}
