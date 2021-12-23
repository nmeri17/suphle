<?php
	namespace Tilwa\Tests\Integration\App\ModuleDescriptor;

	use Tilwa\Tests\Mocks\Interactions\{ModuleThree, ModuleOne, ModuleTwo};

	class AccurateImportTest extends DescriptorCollection {

		public function test_simple_import () {

			$result = $this->getModuleFor(ModuleTwo::class)

			->getShallowValue(); // when

			$moduleThree = $this->getModuleFor(ModuleThree::class);

			$this->assertSame($result, $moduleThree->getLocalValue()); // then
		}

		public function test_intermediary_module_can_access_nested_module_when_called_by_importer () {
			
			$moduleTwo = $this->getModuleFor(ModuleTwo::class);

			$moduleOne = $this->getModuleFor(ModuleOne::class);

			$moduleTwo->setNestedModuleValue(); // when

			$this->assertSame(
				$moduleTwo->newExternalValue(),

				$moduleOne->getBCounterValue()
			); // then
		}
	}
?>