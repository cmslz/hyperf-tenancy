<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/12 18:39
 */

namespace Cmslz\HyperfTenancy\Commands;


use Hyperf\Command\Command;

class Install extends Command
{
    protected ?string $name = 'tenancy::install';

    protected ?string $signature = 'tenancy::install';

    protected string $description = 'Install cmslz/hyperf-tenancy.';

    public function handle()
    {
        $this->comment('Installing cmslz/hyperf-tenancy');
        $this->call('vendor:publish', [
            '--package' => 'cmslz/hyperf-tenancy',
        ]);
    }
}