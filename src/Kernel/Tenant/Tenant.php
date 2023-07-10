<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/12 0:02
 */

namespace Cmslz\HyperfTenancy\Kernel\Tenant;

use Cmslz\HyperfTenancy\Kernel\Tenancy;
use Exception;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Support\Traits\StaticInstance;
use InvalidArgumentException;
use Cmslz\HyperfTenancy\Kernel\Tenant\Models\Tenants as TenantModel;
use Psr\Http\Message\ServerRequestInterface;

class Tenant
{
    use StaticInstance;

    /**
     * @var string
     */
    protected $id;

    protected TenantModel|null $tenant;

    private function checkIfHttpRequest(): bool
    {
        $request = Context::get(ServerRequestInterface::class);
        if ($request !== null) {
            // 存在 HTTP 请求
            return true;
        }
        // 不存在 HTTP 请求
        return false;
    }

    public function init($id = null, bool $isCheck = true)
    {
        if (empty($id) && $isCheck && $this->checkIfHttpRequest()) {
            $request = ApplicationContext::getContainer()->get(RequestInterface::class);
            $id = $request->header('x-tenant-id');
            if (empty($id)) {
                $id = $request->query('tenant');
            }
            if (empty($id)) {
                $id = Tenancy::domainModel()::tenantIdByDomain($request->header('Host'));
            }
        }
        // 过滤根目录
        if (empty($id) && $isCheck) {
            throw new InvalidArgumentException('The tenant is invalid.');
        }

        /**
         * @var TenantModel $tenant
         */
        $tenant = Tenancy::tenantModel()::query()->where('id', $id)->first();
        if (empty($tenant) && $isCheck) {
            throw new InvalidArgumentException(
                sprintf('The tenant %s is invalid', $id)
            );
        }
        $this->id = $id;
        $this->tenant = $tenant;
        return $tenant;
    }

    public function getId(bool $isCheck = true)
    {
        // 过滤根目录
        if (empty($this->id) && $isCheck) {
            throw new InvalidArgumentException('The tenant is invalid.');
        }
        return $this->id;
    }

    /**
     * 获取当前租户
     * @return TenantModel
     * Created by xiaobai at 2023/2/16 14:42
     */
    public function getTenant(): TenantModel
    {
        return $this->tenant;
    }

    /**
     * 指定租户内执行
     * @param $tenants
     * @param callable $callable
     * @throws Exception
     * Created by xiaobai at 2023/2/14 14:02
     */
    public function runForMultiple($tenans, callable $callback)
    {
        Tenancy::runForMultiple($tenans, $callback);
    }

}