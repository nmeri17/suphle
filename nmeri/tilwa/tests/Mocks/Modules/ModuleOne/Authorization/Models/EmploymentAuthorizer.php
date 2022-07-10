<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Authorization\Models;

	use Tilwa\Adapters\Orms\Eloquent\Condiments\BaseEloquentAuthorizer;

	use Tilwa\Exception\Explosives\UnauthorizedServiceAccess;

	class EmploymentAuthorizer extends BaseEloquentAuthorizer {

		public function retrieved ($model):bool {

			return true;
		}

		protected function isEmployer ($model):bool {

			return $this->authStorage->getId() == $model->employer->user_id;
		}

		public function updating ($model):bool {

			if ($this->isEmployer($model)) // you can only access id/user in the event method, not the constructor. At the time of creation, session hasn't been initialized and user id will be undefined

				return true;

			throw new UnauthorizedServiceAccess;
		}

		public function creating ($model):bool {

			return true;
		}

		public function deleting ($model):bool {

			if (!$this->isEmployer($model))

				throw new UnauthorizedServiceAccess;

			foreach ($this->getChildrenMethods(get_class($model)) as $methodName)

				$model->$methodName()->delete();

			return true;
		}
	}
?>