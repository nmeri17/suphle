<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\ApiRoutes\V3;

	use Suphle\Routing\BaseApiCollection;

	use Suphle\Response\Format\Json;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Controllers\Versions\V3\ApiUpdate3Controller;

	class ApiUpdate3Entry extends BaseApiCollection {

		public function _handlingClass ():string {

			return ApiUpdate3Controller::class;
		}

		public function CASCADE () {

			$this->_get(new Json("thirdCascade"));
		}
	}
?>