<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/13 17:13
 */

namespace Cmslz\HyperfTenancy\Commands;

use Cmslz\HyperfTenancy\Concerns\HasATenantsOption;
use Hyperf\Database\Commands\Migrations\RollbackCommand;

class Rollback extends RollbackCommand
{
    use HasATenantsOption;

    /**
     * for hyperf command
     * @var string
     */
    protected ?string $name = 'tenants:rollback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Rollback migrations for tenant(s).';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        tenancy()->runForMultiple($this->input->getOption('tenants'), function ($tenant) {
            $this->line("Tenant: {$tenant['id']}");
            // Rollback
            parent::handle();
        });
    }
}