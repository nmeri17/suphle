<?php
	namespace Tilwa\Tests\Integration\App\ModuleDescriptor;

	use Tilwa\Hydration\Container;

	use Tilwa\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

	class InadequatePairTest extends FailingCollection {

		protected function setModuleTwo ():void {

			$this->moduleTwo = (new ModuleTwoDescriptor(new Container));
		}
	}
?>