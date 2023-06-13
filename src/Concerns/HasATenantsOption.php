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
    protected function getOptions()
    {
        return array_merge([
            ['tenants', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, '', null],
        ], /*method_exists($this, 'getOptions')?parent::getOptions():*/[]);
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
