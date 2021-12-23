<?php
	namespace Tilwa\Tests\Integration\App\ModuleDescriptor;

	use Tilwa\Hydration\Container;

	use Tilwa\Tests\Mocks\Interactions\ModuleThree;

	use Tilwa\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

	class IncompatiblePairTest extends DescriptorCollection {

		protected function setModuleTwo ():void {

			$this->moduleTwo = (new ModuleTwoDescriptor(new Container))

			->sendExpatriates([

				ModuleThree::class => $this->moduleOne
			]);
		}
	}
?>