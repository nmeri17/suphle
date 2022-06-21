<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleThree\Routes;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleThree\Controllers\BaseController;

	use Tilwa\Response\Format\Json;

	class BrowserCollection extends BaseCollection {

		public function _handlingClass ():string {

			return BaseController::class;
		}
		
		public function _prefixCurrent ():string {
			
			return "MODULE__THREEh";
		}

		public function id () {

			$this->_get(new Json("checkPlaceholder"));
		}
	}
?>