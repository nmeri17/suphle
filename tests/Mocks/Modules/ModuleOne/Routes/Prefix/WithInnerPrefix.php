<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

	use Suphle\Routing\BaseCollection;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\NestedController;

	use Suphle\Response\Format\Json;

	class WithInnerPrefix extends BaseCollection {

		public function _handlingClass ():string {

			return NestedController::class;
		}
		
		public function _prefixCurrent ():string {
			
			return empty($this->parentPrefix) ? "INNER": "";
		}
		
		public function WITH () {
			
			$this->_get(new Json("hasInner"));
		}
	}
?>