<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

	use Suphle\Routing\BaseCollection;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix\IntermediaryToThird;

	class ActualEntry extends BaseCollection {
		
		public function FIRST () {
			
			$this->_prefixFor(IntermediaryToThird::class);
		}
	}
?>