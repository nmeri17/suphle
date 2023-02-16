<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix\Secured;

	use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Prefix\UnchainParentSecurity, Coordinators\BaseCoordinator};

	#[HandlingCoordinator(BaseCoordinator::class)]
	class UpperCollection extends BaseCollection {

		public function _authenticatedPaths():array {

			return ["PREFIX"];
		}
		
		public function PREFIX () {
			
			$this->_prefixFor(UnchainParentSecurity::class);
		}
	}
?>