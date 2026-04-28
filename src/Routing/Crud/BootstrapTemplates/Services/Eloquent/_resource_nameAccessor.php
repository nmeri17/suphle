<?php
namespace _modules_shell\_module_name\Services\Eloquent;

use Suphle\Contracts\Services\{CallInterceptors\MultiUserModelEdit, Models\IntegrityModel};
use Suphle\Services\Structures\BaseErrorCatcherService;
use Suphle\Services\Decorators\{InterceptsCalls, VariableDependencies, DomainService};
use _database_namespace\_resource_name;

#[DomainService(mutation: true)]
#[InterceptsCalls(MultiUserModelEdit::class)]
#[VariableDependencies(["setPayloadStorage", "setRouteInfo"])]
class _resource_nameAccessor implements MultiUserModelEdit
{
    use BaseErrorCatcherService;

    public function __construct(protected readonly _resource_name $blankModel) {}

    public function createSingle(array $modelProperties): object
    {
        return $this->blankModel->create($modelProperties);
    }

    public function paginate(int $limit = null): iterable
    {
        return $this->blankModel->paginate($limit);
    }

    /**
     * Required by MultiUserModelEdit to fetch the record and check updated_at
     */
    public function getResource(object $builder): IntegrityModel
    {
        return $builder->firstOrFail();
    }

    /**
     * Wrapped in a transaction by the Interceptor
     */
    public function updateResource(object $builder, array $toUpdate)
    {
        return $this->getResource($builder)->update($toUpdate);
    }

    public function deleteResource(object $builder): bool
    {
        return $builder->delete();
    }
}