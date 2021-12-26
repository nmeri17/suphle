<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

	use Tilwa\Routing\BaseCollection;

	class IntermediaryToThird extends BaseCollection {
		
		public function MIDDLE () {
			
			$this->_prefixFor(ThirdSegmentCollection::class);
		}
	}
?>