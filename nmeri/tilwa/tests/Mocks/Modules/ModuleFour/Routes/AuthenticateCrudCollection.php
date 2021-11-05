<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleFour\Routes;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleFour\Controllers\BaseController;

	class AuthenticateCrudCollection extends BaseCollection {

		public function _handlingClass ():string {

			return BaseController::class;
		}
		
		public function SECURE__SOMEh () {
			
			$this->_crud("secure-some")->save();
		}

		public function _authenticatedPaths():array {

			return ["EDIT_id"];
		}
	}
?>