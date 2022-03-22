<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Auth;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Request\PathAuthorizer;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\BaseController;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Authorization\Paths\{ModelEditRule, AdminRule};

	use Tilwa\Response\Format\Json;

	class UnlocksAuthorization1 extends BaseCollection {

		public function _handlingClass ():string {

			return BaseController::class;
		}

		public function RETAIN () {

			$this->_get(new Json("plainSegment"));
		}

		public function ADDITIONAL__RULEh () {

			$this->_get(new Json("plainSegment"));
		}

		public function SECEDE () {

			$this->_get(new Json("plainSegment"));
		}

		public function _authorizePaths (PathAuthorizer $pathAuthorizer):void {

			$pathAuthorizer->addRule (

				[ "ADDITIONAL__RULEh"], ModelEditRule::class
			)
			->forgetRule(["SECEDE"], AdminRule::class);
		}
	}
?>