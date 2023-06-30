<?php
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/13 17:02
 */

namespace Cmslz\HyperfTenancy\Commands;


use Cmslz\HyperfTenancy\Concerns\HasATenantsOption;
use Cmslz\HyperfTenancy\Kernel\Tenancy;
use Hyperf\Database\Commands\Migrations\MigrateCommand;
use Hyperf\Database\Migrations\Migrator;

class MigrateMigration extends MigrateCommand
{
    use HasATenantsOption;

    public function __construct(Migrator $migrator)
    {
        $this->migrator = $migrator;
        parent::__construct($migrator);
        parent::setName('tenants:migrate');
    }

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Run migrations for tenant(s)';

    /**
     * Execute the console command.
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        Tenancy::runForMultiple($this->input->getOption('tenants'), function ($tenant) {
            $this->line("Tenant: {$tenant['id']}");

            if (!$this->confirmToProceed()) {
                return;
            }
            $this->input->setOption('database', Tenancy::initDbConnectionName(Tenancy::getTenantDbPrefix()));
            parent::handle();
        });
    }
}