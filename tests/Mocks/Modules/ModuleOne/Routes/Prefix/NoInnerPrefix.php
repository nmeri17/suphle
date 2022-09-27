<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

	use Suphle\Routing\BaseCollection;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\NestedController;

	use Suphle\Response\Format\Json;

	class NoInnerPrefix extends BaseCollection {

		public function _handlingClass ():string {

			return NestedController::class;
		}
		
		public function WITHOUT () {
			
			$this->_get(new Json("noInner"));
		}
	}
?>