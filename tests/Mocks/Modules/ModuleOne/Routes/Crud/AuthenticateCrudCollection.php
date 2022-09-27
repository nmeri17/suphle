<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Crud;

	use Suphle\Routing\BaseCollection;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\CrudController;

	class AuthenticateCrudCollection extends BaseCollection {

		public function _handlingClass ():string {

			return CrudController::class;
		}
		
		public function SECURE__SOMEh () {
			
			$this->_crud("secure-some")->registerCruds();
		}

		public function _authenticatedPaths():array {

			return ["EDIT_id"];
		}
	}
?>