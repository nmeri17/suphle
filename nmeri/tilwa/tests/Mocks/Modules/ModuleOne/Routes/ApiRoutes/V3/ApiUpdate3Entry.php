<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\ApiRoutes\V3;

	use Tilwa\Routing\BaseApiCollection;

	use Tilwa\Response\Format\Json;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\Versions\V3\ApiUpdate3Controller;

	class ApiUpdate3Entry extends BaseApiCollection {

		public function _handlingClass ():string {

			return ApiUpdate3Controller::class;
		}

		public function CASCADE () {

			$this->_get(new Json("thirdCascade"));
		}
	}
?>