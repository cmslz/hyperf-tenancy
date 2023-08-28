<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/12 0:11
 */

namespace Cmslz\HyperfTenancy\Kernel\Tenant\Models;

use Cmslz\HyperfTenancy\Kernel\Exceptions\TenancyException;
use Hyperf\Collection\Collection;
use Hyperf\Context\Context;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

/**
 * @property string $id
 * @property string $data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Tenants extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'tenants';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = ['id', 'data', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected array $casts = [
        'id' => 'string',
        'data' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public static function tenantsAll(string $id = null, bool $reset = false)
    {
        $tenants = Context::get(self::class);
        if (empty($tenants) || $reset) {
            $tenants = self::query()->orderBy('created_at')->get();
            Context::set(self::class, $tenants);
        }
        if (!empty($id)) {
            $tenant = Collection::make($tenants)->where('id', $id)->first();
            if (empty($tenant)) {
                if ($reset) {
                    throw new TenancyException(
                        sprintf('The tenant %s is invalid', $id)
                    );
                }
                return self::tenantsAll($id, true);
            }
            return $tenant;
        }
        return $tenants;
    }
}
