<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleFive\Routes;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleFive\Controllers\BaseController;

	use Tilwa\Response\Format\Json;

	class SecureBrowserCollection extends BaseCollection {

		public function _handlingClass ():string {

			return BaseController::class;
		}

		public function SEGMENT() {

			$this->_get(new Json("plainSegment"));
		}

		public function _authenticatedPaths():array {

			return ["SEGMENT"];
		}
	}
?>