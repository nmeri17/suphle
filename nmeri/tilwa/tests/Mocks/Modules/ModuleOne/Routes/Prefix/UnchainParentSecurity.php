<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\MixedNestedSecuredController;

	class UnchainParentSecurity extends BaseCollection {

		public function _handlingClass ():string {

			return MixedNestedSecuredController::class;
		}

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