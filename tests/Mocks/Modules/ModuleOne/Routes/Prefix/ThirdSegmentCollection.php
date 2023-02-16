<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

	use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\NestedController;

	use Suphle\Response\Format\Json;

	#[HandlingCoordinator(NestedController::class)]
	class ThirdSegmentCollection extends BaseCollection {

		public function THIRD () {
			
			$this->_get(new Json("thirdSegmentHandler"));
		}
	}
?>