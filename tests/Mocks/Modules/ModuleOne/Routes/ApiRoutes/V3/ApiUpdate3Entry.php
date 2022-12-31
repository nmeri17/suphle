<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\ApiRoutes\V3;

	use Suphle\Routing\BaseApiCollection;

	use Suphle\Response\Format\Json;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\Versions\V3\ApiUpdate3Coordinator;

	class ApiUpdate3Entry extends BaseApiCollection {

		public function _handlingClass ():string {

			return ApiUpdate3Coordinator::class;
		}

		public function CASCADE () {

			$this->_get(new Json("thirdCascade"));
		}
	}
?>