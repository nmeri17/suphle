<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix\Secured;

	use Suphle\Routing\BaseCollection;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Prefix\UnchainParentSecurity, Coordinators\BaseCoordinator};

	class UpperCollection extends BaseCollection {

		public function _handlingClass ():string {

			BaseCoordinator::class;
		}

		public function _authenticatedPaths():array {

			return ["PREFIX"];
		}
		
		public function PREFIX () {
			
			$this->_prefixFor(UnchainParentSecurity::class);
		}
	}
?>