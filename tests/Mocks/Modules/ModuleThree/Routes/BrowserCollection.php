<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleThree\Routes;

	use Suphle\Routing\BaseCollection;

	use Suphle\Tests\Mocks\Modules\ModuleThree\Coordinators\BaseCoordinator;

	use Suphle\Response\Format\Json;

	class BrowserCollection extends BaseCollection {

		public function _handlingClass ():string {

			return BaseCoordinator::class;
		}
		
		public function _prefixCurrent ():string {
			
			return "MODULE__THREEh";
		}

		public function id () {

			$this->_get(new Json("checkPlaceholder"));
		}
	}
?>