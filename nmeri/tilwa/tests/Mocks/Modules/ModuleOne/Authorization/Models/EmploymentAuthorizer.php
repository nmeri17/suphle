<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Authorization\Models;

	use Tilwa\Contracts\Auth\ModelAuthorities;

	use Tilwa\Exception\Explosives\UnauthorizedServiceAccess;

	class EmploymentAuthorizer implements ModelAuthorities {

		private $user;

		public function __construct (AuthStorage $authStorage) {

			$this->user = $authStorage->getUser();
		}

		public function retrieved ($model):bool {

			return true;
		}

		public function updating ($model):bool {

			return $this->user->getId() == $model->employer->user_id;
		}

		public function creating ($model):bool {

			return true;
		}

		public function deleting ($model):bool {

			return true;
		}
	}
?>