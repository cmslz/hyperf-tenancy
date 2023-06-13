<?php
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/13 17:02
 */

namespace Cmslz\HyperfTenancy\Commands;


use Cmslz\HyperfTenancy\Concerns\ConfirmToProceed;
use Cmslz\HyperfTenancy\Concerns\DealsWithMigrations;
use Cmslz\HyperfTenancy\Concerns\HasATenantsOption;
use Hyperf\Database\Commands\Migrations\MigrateCommand;

class Migrate extends MigrateCommand
{
    use HasATenantsOption,
        DealsWithMigrations,
        ConfirmToProceed;

    /**
     * for hyperf command
     * @var string
     */
    protected ?string $name = 'tenants:migrate';

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
        tenancy()->runForMultiple($this->input->getOption('tenants'), function ($tenant) {
            $this->line("Tenant: {$tenant['id']}");
            parent::handle();
        });
    }
}