<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\ApiRoutes\V2;

	use Suphle\Routing\{BaseApiCollection, Decorators\HandlingCoordinator};

	use Suphle\Response\Format\Json;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\Versions\V2\ApiUpdate2Coordinator;

	#[HandlingCoordinator(ApiUpdate2Coordinator::class)]
	class ApiUpdate2Entry extends BaseApiCollection {

		public function CASCADE () {

			$this->_get(new Json("secondCascade"));
		}

		public function SEGMENT__IN__SECONDh () {

			$this->_get(new Json("segmentInSecond"));
		}
	}
?>