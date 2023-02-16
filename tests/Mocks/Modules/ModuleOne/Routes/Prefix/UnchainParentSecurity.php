<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

	use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

	use Suphle\Response\Format\Json;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\MixedNestedSecuredController;

	#[HandlingCoordinator(MixedNestedSecuredController::class)]
	class UnchainParentSecurity extends BaseCollection {

		public function _authenticatedPaths():array {

			return ["RETAIN__AUTHh"];
		}
		
		public function UNLINK () {
			
			$this->_get(new Json("handleUnlinked"));
		}
		
		public function RETAIN__AUTHh () {
			
			$this->_get(new Json("handleRetained"));
		}
	}
?>