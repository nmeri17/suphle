<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\NestedController;

	use Tilwa\Response\Format\Json;

	class WithInnerPrefix extends BaseCollection {

		public function _handlingClass ():string {

			return NestedController::class;
		}
		
		public function _prefixCurrent ():string {
			
			return empty($this->parentPrefix) ? "inner": "";
		}
		
		public function WITH () {
			
			$this->_get(new Json("hasInner"));
		}
	}
?>