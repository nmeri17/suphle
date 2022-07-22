<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Middlewares;

	use Suphle\Routing\BaseCollection;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Controllers\BaseController;

	use Suphle\Response\Format\Json;

	class RetainsMiddleware extends BaseCollection {

		public function _handlingClass ():string {

			return BaseController::class;
		}

		public function SEGMENT () {

			$this->_get(new Json("plainSegment"));
		}
	}
?>