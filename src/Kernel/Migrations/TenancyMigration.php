<?php
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/30 9:54
 */

namespace Cmslz\HyperfTenancy\Kernel\Migrations;


use Cmslz\HyperfTenancy\Kernel\Tenancy;
use Hyperf\Database\Migrations\Migration;

/**
 * 租户迁移文件
 * Class TenancyMigration
 * @package Cmslz\HyperfTenancy\Kernel\Migrations
 */
abstract class TenancyMigration extends Migration
{
    public function getConnection(): string
    {
        return Tenancy::initDbConnectionName(Tenancy::getTenantDbPrefix());
    }
}