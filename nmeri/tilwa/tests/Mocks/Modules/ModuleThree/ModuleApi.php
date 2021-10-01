<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleThree;

	use Tilwa\Tests\Mocks\Interactions\ModuleThree;

	class ModuleApi implements ModuleThree {

		public function getDValue ():int {

			return 10;
		}
	}
?>