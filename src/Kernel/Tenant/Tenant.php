<?php

declare(strict_types=1);
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/6/12 0:02
 */

namespace Cmslz\HyperfTenancy\Kernel\Tenant;

use Cmslz\HyperfTenancy\Kernel\Exceptions\TenancyException;
use Cmslz\HyperfTenancy\Kernel\Tenancy;
use Exception;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Contract\RequestInterface;
use Cmslz\HyperfTenancy\Kernel\Tenant\Models\Tenants as TenantModel;
use Hyperf\Support\Traits\StaticInstance;

class Tenant
{
    use StaticInstance;

    protected TenantModel|null $tenant;

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws TenancyException
     */
    public function init($id = null, bool $isCheck = true)
    {
        if (empty($id) && $isCheck && Tenancy::checkIfHttpRequest()) {
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
            throw new TenancyException('The tenant is invalid.');
        }

        /**
         * @var TenantModel $tenant
         */
        $tenant = Tenancy::tenantModel()::query()->where('id', $id)->first();
        if (empty($tenant) && $isCheck) {
            Context::destroy(Tenancy::getContextKey());
            throw new TenancyException(
                sprintf('The tenant %s is invalid', $id)
            );
        }
        Context::set(Tenancy::getContextKey(), $tenant);
        return $tenant;
    }

    public function getTenant(): ?TenantModel
    {
        $tenant = Context::get(Tenancy::getContextKey());
        if (empty($tenant)) {
            return null;
        }
        return $tenant;
    }

    /**
     * @param bool $isCheck
     * @return string|null
     * @throws TenancyException
     * Created by xiaobai at 2023/8/3 13:47
     */
    public function getId(bool $isCheck = true): ?string
    {
        $tenant = $this->getTenant();
        // 过滤根目录
        if (empty($tenant) && $isCheck) {
            throw new TenancyException('The tenant is invalid.');
        }
        return $tenant->id ?? null;
    }

    /**
     * 指定租户内执行
     * @param $tenants
     * @param callable $callable
     * @throws Exception
     * Created by xiaobai at 2023/2/14 14:02
     */
    public function runForMultiple($tenants, callable $callable)
    {
        Tenancy::runForMultiple($tenants, $callable);
    }

}