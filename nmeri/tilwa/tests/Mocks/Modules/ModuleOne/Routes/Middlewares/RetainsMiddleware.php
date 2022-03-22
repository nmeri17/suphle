<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Middlewares;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\BaseController;

	use Tilwa\Response\Format\Json;

	class RetainsMiddleware extends BaseCollection {

		public function _handlingClass ():string {

			return BaseController::class;
		}

		public function SEGMENT () {

			$this->_get(new Json("plainSegment"));
		}
	}
?>