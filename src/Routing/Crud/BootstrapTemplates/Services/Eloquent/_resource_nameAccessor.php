<?php
namespace _modules_shell\_module_name\Services\Eloquent;

use Suphle\Contracts\Services\Models\IntegrityModel;

use Suphle\Services\Structures\BaseMultiUserModelEdit;

use _database_namespace\_resource_name;

class _resource_nameAccessor extends BaseMultiUserModelEdit {

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