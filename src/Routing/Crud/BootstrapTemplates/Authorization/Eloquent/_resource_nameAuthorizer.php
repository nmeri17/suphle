<?php
namespace _modules_shell\_module_name\Authorization\Eloquent;

use Suphle\Adapters\Orms\Eloquent\Condiments\BaseEloquentAuthorizer;

use Suphle\Exception\Explosives\UnauthorizedServiceAccess;

class _resource_nameAuthorizer extends BaseEloquentAuthorizer {

	public function retrieved ($model):bool {

		return true;
	}

	/**
	 * Model's invariant
	 * 
	 * @param $model Suphle\Adapters\Orms\Eloquent\Models\BaseModel
	*/
	protected function canModify ($model):bool {

		return $this->authStorage->getId() == $model->user_id;
	}

	public function updating ($model):bool {

		if ($this->canModify($model))

			return true;

		throw new UnauthorizedServiceAccess;
	}

	public function creating ($model):bool {

		return true;
	}

	public function deleting ($model):bool {

		if (!$this->canModify($model))

			throw new UnauthorizedServiceAccess;

		foreach ($this->getChildrenMethods(get_class($model)) as $methodName)

			$model->$methodName()->delete();

		return true;
	}
}