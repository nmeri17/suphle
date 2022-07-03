<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Auth;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Request\PathAuthorizer;

	use Tilwa\Response\Format\Json;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\MultiEditController;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Authorization\Paths\{ModelEditRule, AdminRule};

	class UnlocksAuthorization1 extends BaseCollection {

		public function _handlingClass ():string {

			return MultiEditController::class;
		}

		public function RETAIN () {

			$this->_get(new Json("simpleResult"));
		}

		public function ADDITIONAL__RULEh () {

			$this->_get(new Json("simpleResult"));
		}

		public function SECEDE () {

			$this->_get(new Json("simpleResult"));
		}

		public function GMULTI__EDIT__AUTHh () {

			$this->_get(new Json("getEditableResource"));
		}

		public function GMULTI__EDIT__UNAUTHh () {

			$this->_get(new Json("getEditableResource"));
		}

		public function _authorizePaths (PathAuthorizer $pathAuthorizer):void {

			$pathAuthorizer->addRule (

				[ "ADDITIONAL__RULEh"], ModelEditRule::class
			)
			->forgetRule([

				"SECEDE", "GMULTI__EDIT__UNAUTHh"
			], AdminRule::class);
		}
	}
?>