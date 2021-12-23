<?php
	namespace Tilwa\Tests\Integration\App;

	use Tilwa\Hydration\Container;

	use Tilwa\Testing\TestTypes\ModuleLevelTest;

	use Tilwa\Tests\Mocks\Interactions\{ModuleThree, ModuleOne, ModuleTwo};

	use Tilwa\Tests\Mocks\Modules\{ModuleTwo\Meta\ModuleTwoDescriptor, ModuleThree\Meta\ModuleThreeDescriptor, ModuleOne\Meta\ModuleOneDescriptor};

	class ModuleDescriptorTest extends ModuleLevelTest {

		private $moduleOne, $moduleTwo, $moduleThree;

		protected function setUp ():void {

			$this->setModuleOne();

			$this->setModuleThree();

			$this->setModuleTwo();

			parent::setUp();
		}

		protected function getModules():array {

			return [
				$this->moduleOne, $this->moduleTwo,

				$this->moduleThree
			];
		}

		private function setModuleThree ():void {

			$this->moduleThree = (new ModuleThreeDescriptor(new Container))

			->sendExpatriates([

				ModuleOne::class => $this->moduleOne
			]);
		}

		private function setModuleOne ():void {

			$this->moduleOne = new ModuleOneDescriptor(new Container);
		}

		private function setModuleTwo ():void {

			$this->moduleTwo = (new ModuleTwoDescriptor(new Container))

			->sendExpatriates([

				ModuleThree::class => $this->moduleThree
			]);
		}

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

		public function test_cant_pair_module_with_invalid_interfaces () {

			// given
		}

		public function test_exportImplements_matches_we_provide () {

			// sut ==> the importer
		}

		public function test_imported_shell_matches_requested_interface () {

			// try to send wrong stuff
		}
	}
?>