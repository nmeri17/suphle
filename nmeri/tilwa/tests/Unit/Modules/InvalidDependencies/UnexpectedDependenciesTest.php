<?php
	namespace Tilwa\Tests\Unit\Modules\InvalidDependencies;

	use Tilwa\Hydration\Container;

	use Tilwa\Exception\Explosives\Generic\UnexpectedModules;

	use Tilwa\Tests\Integration\App\ModuleDescriptor\DescriptorCollection;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

	class ExpectedDependenciesTest extends DescriptorCollection {

		protected function setModuleTwo ():void {

			$this->moduleTwo = (new ModuleTwoDescriptor(new Container))

			->sendExpatriates([

				ModuleOne::class => $this->moduleOne
			]);
		}

		public function test_will_complain_when_missing_dependencies () {

			$this->setExpectedException(UnexpectedModules::class); // then

			// given @see module creation

			$this->moduleTwo->warmUp(); 

			$this->moduleTwo->prepareToRun(); // when
		}
	}
?>