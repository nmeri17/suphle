<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

	use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\NestedController;

	use Suphle\Response\Format\Json;

	#[HandlingCoordinator(NestedController::class)]
	class WithInnerPrefix extends BaseCollection {
		
		public function _prefixCurrent ():string {
			
			return empty($this->parentPrefix) ? "INNER": "";
		}
		
		public function WITH () {
			
			$this->_httpGet(new Json("hasInner"));
		}
	}
?>