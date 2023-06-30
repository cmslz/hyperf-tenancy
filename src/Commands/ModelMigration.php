<?php

namespace Cmslz\HyperfTenancy\Commands;

use Cmslz\HyperfTenancy\Concerns\HasATenantsOption;
use Cmslz\HyperfTenancy\Kernel\Tenancy;
use Hyperf\Database\Commands\ModelCommand;
use Hyperf\Database\Model\Model;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

class ModelMigration extends ModelCommand
{
    use HasATenantsOption;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct($container);
        $this->setDescription('Create new model classes by tenant.');
        parent::setName('tenants:model');
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption('tenants', null, InputOption::VALUE_REQUIRED, 'Which tenant');
    }

    public function handle()
    {
        Tenancy::runForMultiple(Tenancy::baseDatabase(), function ($tenant) {
            $this->line("Tenant: {$tenant['id']}");
            $this->input->setOption('inheritance', '\\' . Model::class);
            $this->input->setOption('pool', Tenancy::initDbConnectionName(Tenancy::getTenantDbPrefix()));
            parent::handle();
        });
    }
}