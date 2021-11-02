<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\ApiRoutes\V1;

	use Tilwa\Routing\BaseApiCollection;

	use Tilwa\Response\Format\Json;

	class LowerMirror extends BaseApiCollection {
		
		public function API__SEGMENTh () {
			
			$this->_get(new Json("segmentHandler"));
		}

		public function SEGMENT_id() {

			$this->_get(new Json("simplePairOverride"));
		}
	}
?>