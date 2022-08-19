<?php
	namespace Suphle\Tests\Integration\Modules\ModuleDescriptor;

	use Suphle\Tests\Mocks\Interactions\{ModuleThree, ModuleOne, ModuleTwo};

	class AccurateImportTest extends DescriptorCollection {

		public function test_simple_import () {

			$result = $this->getModuleFor(ModuleTwo::class)

			->getShallowValue(); // when

			$moduleThree = $this->getModuleFor(ModuleThree::class);

			$this->assertSame($result, $moduleThree->getLocalValue()); // then
		}

		public function test_intermediary_module_can_access_nested_module_when_called_by_importer () {
			
			$moduleTwo = $this->getModuleFor(ModuleTwo::class);

			$payload = 67;

			$moduleTwo->setNestedModuleValue($payload); // when

			$this->assertSame(
				$payload,

				$this->getModuleFor(ModuleOne::class)->getBCounterValue()
			); // then
		}
	}
?>