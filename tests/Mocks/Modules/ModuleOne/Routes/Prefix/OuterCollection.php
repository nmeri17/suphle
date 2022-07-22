<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

	use Suphle\Routing\BaseCollection;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Controllers\BlankController;

	class OuterCollection extends BaseCollection {

		public function _handlingClass ():string {

			return BlankController::class;
		}
		
		public function _prefixCurrent():string {
			
			return "OUTER";
		}
		
		public function USE__METHODh () {
			
			$this->_prefixFor(NoInnerPrefix::class);
		}
		
		public function IGNORE__INTERNALh () {
			
			$this->_prefixFor(WithInnerPrefix::class);
		}
	}
?>