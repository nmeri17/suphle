<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

	use Suphle\Routing\BaseCollection;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\NestedController;

	use Suphle\Response\Format\Json;

	class ThirdSegmentCollection extends BaseCollection {

		public function _handlingClass ():string {

			return NestedController::class;
		}

		public function THIRD () {
			
			$this->_get(new Json("thirdSegmentHandler"));
		}
	}
?>