<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/12 0:01
 */

namespace Cmslz\HyperfTenancy\Kernel\Tenant\Models;


use Cmslz\HyperfTenancy\Kernel\Tenant\CentralConnection;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

/**
 * Class Domain
 * @package Cmslz\HyperfTenancy\Kernel\Tenant\Models
 * @property int $id 自增id
 * @property string $domain 租户域名
 * @property string $tenant_id 关联租户id
 * @property string|null $createdAt 创建时间
 * @property string|null $updatedAt 更新时间
 * @property string|null $deletedAt 删除时间
 */
class Domain extends Model
{
    use SoftDeletes;
    use CentralConnection;

    protected ?string $table = 'tenant_domains';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = ['id', 'domain', 'tenant_id', 'created_at', 'updated_at'];

    /**
     * 获取租户域名
     * @param string $tenantId
     * @return string
     * Created by xiaobai at 2023/2/16 17:46
     */
    public static function domainByTenantId(string $tenantId): string
    {
        $domain = self::query()->where('tenant_id', $tenantId)->value('domain');
        if (empty($domain)) {
            return '';
        }

        $scheme = config('app_env') === 'dev' ? 'http://' : 'https://';
        return $scheme . $domain;
    }
}