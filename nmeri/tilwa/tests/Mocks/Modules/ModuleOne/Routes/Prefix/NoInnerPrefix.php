<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\NestedController;

	use Tilwa\Response\Format\Json;

	class NoInnerPrefix extends BaseCollection {

		public function _handlingClass ():string {

			return NestedController::class;
		}
		
		public function WITHOUT () {
			
			$this->_get(new Json("noInner"));
		}
	}
?>