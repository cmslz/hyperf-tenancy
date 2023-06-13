<?php
declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/13 17:05
 */

namespace Cmslz\HyperfTenancy\Concerns;


trait DealsWithMigrations
{
    protected function getMigrationPaths()
    {
        if ($this->input->hasOption('path') && $this->input->getOption('path')) {
            return parent::getMigrationPaths();
        }

        return BASE_PATH.'/database/migrations/tenant';//database_path('migrations/tenant');
    }
}
