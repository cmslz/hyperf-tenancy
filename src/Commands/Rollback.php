<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/13 17:13
 */

namespace Cmslz\HyperfTenancy\Commands;

use Cmslz\HyperfTenancy\Concerns\HasATenantsOption;
use Hyperf\Database\Commands\Migrations\RollbackCommand;
use Hyperf\Database\Migrations\Migrator;

class Rollback extends RollbackCommand
{
    use HasATenantsOption;

    public function __construct(Migrator $migrator)
    {
        $this->migrator = $migrator;
        parent::__construct($migrator);
        parent::setName('tenants:rollback');
    }

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Rollback migrations for tenant(s).';

    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        tenancy()->runForMultiple($this->input->getOption('tenants'), function ($tenant) {
            $this->line("Tenant: {$tenant['id']}");
            parent::handle();
        });
    }
}