<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Authorization\Paths;

	use Suphle\Contracts\Auth\AuthStorage;

	use Suphle\Request\RouteRule;

	use Suphle\Routing\PathPlaceholders;

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	class EmploymentEditRule extends RouteRule {

		private $model, $pathPlaceholders;

		public function __construct (AuthStorage $authStorage, Employment $model, PathPlaceholders $pathPlaceholders) {

			$this->model = $model;

			$this->pathPlaceholders = $pathPlaceholders;

			parent::__construct($authStorage);
		}

		public function permit ():bool {

			$user = $this->authStorage->getUser();

			return /*$user->isAdmin() ||*/ $user->getId() == $this->getCreatorId(); // not necessary cuz of the preceding rule combined with this
		}

		protected function getCreatorId ():int {

			return $this->model->find(
			
				$this->pathPlaceholders->getSegmentValue("id")
			)->employer->user_id;
		}
	}
?>