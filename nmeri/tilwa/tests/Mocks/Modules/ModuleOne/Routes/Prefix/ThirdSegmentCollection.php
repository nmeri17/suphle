<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\NestedController;

	use Tilwa\Response\Format\Json;

	class ThirdSegmentCollection extends BaseCollection {

		public function _handlingClass ():string {

			return NestedController::class;
		}

		public function THIRD () {
			
			$this->_get(new Json("thirdSegmentHandler"));
		}
	}
?>