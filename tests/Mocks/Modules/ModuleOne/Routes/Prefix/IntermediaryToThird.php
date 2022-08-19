<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

	use Suphle\Routing\BaseCollection;

	class IntermediaryToThird extends BaseCollection {
		
		public function MIDDLE () {
			
			$this->_prefixFor(ThirdSegmentCollection::class);
		}
	}
?>