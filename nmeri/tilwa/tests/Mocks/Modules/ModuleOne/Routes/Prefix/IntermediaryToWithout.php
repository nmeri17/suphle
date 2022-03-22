<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

	use Tilwa\Routing\BaseCollection;

	class IntermediaryToWithout extends BaseCollection {
		
		public function MIDDLE () {
			
			$this->_prefixFor(NoInnerPrefix::class);
		}
	}
?>