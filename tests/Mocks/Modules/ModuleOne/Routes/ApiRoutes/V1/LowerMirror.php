<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\ApiRoutes\V1;

	use Suphle\Routing\BaseApiCollection;

	use Suphle\Response\Format\Json;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Controllers\ApiEntryController;

	class LowerMirror extends BaseApiCollection {

		public function _handlingClass ():string {

			return ApiEntryController::class;
		}
		
		public function API__SEGMENTh () {
			
			$this->_get(new Json("segmentHandler"));
		}

		public function SEGMENT_id() {

			$this->_get(new Json("simplePairOverride"));
		}

		public function CASCADE () {

			$this->_get(new Json("originalCascade"));
		}

		public function SECURE__SEGMENTh () {

			$this->_get(new Json("segmentHandler"));
		}

		public function _authenticatedPaths():array {

			return ["SECURE__SEGMENTh"];
		}
	}
?>