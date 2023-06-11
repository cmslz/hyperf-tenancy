<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/12 0:11
 */

namespace Cmslz\HyperfTenancy\Kernel\Tenant\Models;

use Cmslz\HyperfTenancy\Kernel\Tenant\CentralConnection;
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
    use CentralConnection;
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
}
