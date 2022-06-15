<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Authorization\Models;

	use Tilwa\Contracts\Auth\{ModelAuthorities, AuthStorage};

	use Tilwa\Exception\Explosives\UnauthorizedServiceAccess;

	class EmploymentAuthorizer implements ModelAuthorities {

		private $authStorage;

		public function __construct (AuthStorage $authStorage) {

			$this->authStorage = $authStorage;
		}

		public function retrieved ($model):bool {

			return true;
		}

		public function updating ($model):bool {

			if ($this->authStorage->getId() == $model->employer->user_id) // you can only access id/user in the event method, not the constructor. At the time of creation, session hasn't been initialized and user id will be undefined

				return true;

			throw new UnauthorizedServiceAccess;
		}

		public function creating ($model):bool {

			return true;
		}

		public function deleting ($model):bool {

			return true;
		}
	}
?>