<?php
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/7/1 22:11
 */

namespace Cmslz\HyperfTenancy\Commands;


use Cmslz\HyperfTenancy\Kernel\Migrations\MigrationCreator;
use Hyperf\Database\Commands\Migrations\GenMigrateCommand;

class GenMigration extends GenMigrateCommand
{
    /**
     * Create a new migration install command instance.
     */
    public function __construct(MigrationCreator $creator)
    {
        parent::__construct($creator);
        parent::setName('tenants:create');
    }


    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Run migrations for tenant(s)';
}