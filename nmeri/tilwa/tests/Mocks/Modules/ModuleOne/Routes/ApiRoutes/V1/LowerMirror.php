<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\ApiRoutes\V1;

	use Tilwa\Routing\BaseApiCollection;

	use Tilwa\Response\Format\Json;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\ApiEntryController;

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
	}
?>