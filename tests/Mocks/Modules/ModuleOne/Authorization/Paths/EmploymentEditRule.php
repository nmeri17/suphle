<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Authorization\Paths;

	use Suphle\Contracts\Auth\AuthStorage;

	use Suphle\Request\RouteRule;

	use Suphle\Routing\PathPlaceholders;

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	class EmploymentEditRule extends RouteRule {

		public function __construct (AuthStorage $authStorage, private readonly Employment $model, private readonly PathPlaceholders $pathPlaceholders) {

			parent::__construct($authStorage);
		}

		public function permit ():bool {

			return /*$user->isAdmin() &&*/ $this->authStorage->getId() == $this->getCreatorId(); // not necessary cuz of the preceding rule combined with this, unless we want to replace "&&" with "||"
		}

		protected function getCreatorId ():int {

			$employment = $this->model->find(
			
				$this->pathPlaceholders->getSegmentValue("id")
			);

			return $employment->employer->user_id;
		}
	}
?>