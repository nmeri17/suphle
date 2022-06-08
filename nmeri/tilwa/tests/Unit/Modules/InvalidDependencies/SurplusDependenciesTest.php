<?php
	namespace Tilwa\Tests\Unit\Modules\InvalidDependencies;

	use Tilwa\Hydration\Container;

	use Tilwa\Exception\Explosives\Generic\UnexpectedModules;

	use Tilwa\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

	use Tilwa\Tests\Mocks\Interactions\{ModuleThree, ModuleOne};

	use Tilwa\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

	class SurplusDependenciesTest extends DescriptorCollection {

		protected function setModuleTwo ():void {

			$this->moduleTwo = (new ModuleTwoDescriptor(new Container))

			->sendExpatriates([

				ModuleThree::class => $this->moduleThree,

				ModuleOne::class => $this->moduleOne
			]);
		}

		public function test_will_reject_surplus_dependencies () {

			$this->expectException(UnexpectedModules::class); // then

			// given @see module creation

			$this->moduleTwo->warmModuleContainer(); 

			$this->moduleTwo->prepareToRun(); // when
		}
	}
?>