<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\ApiRoutes\V2;

	use Tilwa\Routing\BaseApiCollection;

	use Tilwa\Response\Format\Json;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\Versions\V2\ApiUpdate2Controller;

	class ApiUpdate2Entry extends BaseApiCollection {

		public function _handlingClass ():string {

			return ApiUpdate2Controller::class;
		}

		public function CASCADE () {

			$this->_get(new Json("secondCascade"));
		}

		public function SEGMENT__IN__SECONDh () {

			$this->_get(new Json("segmentInSecond"));
		}
	}
?>