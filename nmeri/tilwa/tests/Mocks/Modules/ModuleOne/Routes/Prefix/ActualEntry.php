<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Prefix\IntermediaryToThird;

	class ActualEntry extends BaseCollection {
		
		public function FIRST () {
			
			$this->_prefixFor(IntermediaryToThird::class);
		}
	}
?>