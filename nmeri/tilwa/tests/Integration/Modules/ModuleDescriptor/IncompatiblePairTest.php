<?php
	namespace Tilwa\Tests\Integration\Modules\ModuleDescriptor;

	use Tilwa\Hydration\Container;

	use Tilwa\Tests\Mocks\Interactions\ModuleThree;

	use Tilwa\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

	class IncompatiblePairTest extends FailingCollection {

		protected function setModuleTwo ():void {

			$this->moduleTwo = (new ModuleTwoDescriptor(new Container))

			->sendExpatriates([

				ModuleThree::class => $this->moduleOne
			]);
		}
	}
?>