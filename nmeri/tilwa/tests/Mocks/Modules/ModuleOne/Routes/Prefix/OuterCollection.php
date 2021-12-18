<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\BlankController;

	class OuterCollection extends BaseCollection {

		public function _handlingClass ():string {

			return BlankController::class;
		}
		
		public function _prefixCurrent():string {
			
			return "outer";
		}
		
		public function USE__METHODh () {
			
			$this->_prefixFor(NoInnerPrefix::class);
		}
		
		public function IGNORE__INTERNALh () {
			
			$this->_prefixFor(WithInnerPrefix::class);
		}
	}
?>