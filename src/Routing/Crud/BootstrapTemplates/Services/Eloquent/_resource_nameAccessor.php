<?php

namespace _modules_shell\_module_name\Services\Eloquent;

use Suphle\Services\UpdatefulService;

use Suphle\Contracts\Services\{CallInterceptors\MultiUserModelEdit, Models\IntegrityModel};

use Suphle\Contracts\Routing\Crud\ResourceMultiEdit;

use Suphle\Services\{UpdatefulService, Structures\BaseErrorCatcherService};

use Suphle\Services\Decorators\{InterceptsCalls, VariableDependencies};

use _database_namespace\_resource_name;

#[InterceptsCalls(MultiUserModelEdit::class)]
#[VariableDependencies([

    "setPayloadStorage", "setPlaceholderStorage"
])]
class _resource_nameAccessor extends UpdatefulService implements ResourceMultiEdit { // idk if systemEdit is used in the crud context. It seems like mainly get/update; so no need for an accessor wrapper

	use BaseErrorCatcherService;

	public function __construct (protected readonly _resource_name $blankModel) {

		//
	}

	public function createSingle (array $modelProperties):object {

		return $this->blankModel

		->create($this->payloadStorage->fullPayload());
	}

	public function paginate (int $limit = null):iterable {

		return $this->blankModel->paginate($limit);
	}

	public function getResource(object $builder): IntegrityModel
    {

        return $builder->first();
    }

    public function updateResource(object $builder, array $toUpdate)
    {

        return $builder->update($toUpdate);
    }

	public function deleteById (string $id):bool {

		return $this->blankModel->where(compact("id"))->delete();
	}
}