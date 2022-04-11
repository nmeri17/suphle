<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Prefix\Secured;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Prefix\UnchainParentSecurity, Controllers\BaseController};

	class UpperCollection extends BaseCollection {

		public function _handlingClass ():string {

			BaseController::class;
		}

		public function _authenticatedPaths():array {

			return ["PREFIX"];
		}
		
		public function PREFIX () {
			
			$this->_prefixFor(UnchainParentSecurity::class);
		}
	}
?>