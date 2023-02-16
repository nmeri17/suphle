<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Crud;

	use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\CrudCoordinator;

	#[HandlingCoordinator(CrudCoordinator::class)]
	class AuthenticateCrudCollection extends BaseCollection {
		
		public function SECURE__SOMEh () {
			
			$this->_crud("secure-some")->registerCruds();
		}

		public function _authenticatedPaths():array {

			return ["EDIT_id"];
		}
	}
?>