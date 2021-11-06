<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\CrudController;

	class AuthenticateCrudCollection extends BaseCollection {

		public function _handlingClass ():string {

			return CrudController::class;
		}
		
		public function SECURE__SOMEh () {
			
			$this->_crud("secure-some")->save();
		}

		public function _authenticatedPaths():array {

			return ["EDIT_id"];
		}
	}
?>