<?php
	namespace Tilwa\Tests\Integration\App\ModuleDescriptor;

	use Tilwa\Hydration\Container;

	use Tilwa\Tests\Mocks\Interactions\{ModuleThree, ModuleOne };

	use Tilwa\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

	class SurplusPairTest extends FailingCollection {

		protected function setModuleTwo ():void {

			$this->moduleTwo = (new ModuleTwoDescriptor(new Container))

			->sendExpatriates([

				ModuleThree::class => $this->moduleThree,

				ModuleOne::class => $this->moduleOne
			]);
		}
	}
?>