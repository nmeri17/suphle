<?php
	namespace Tilwa\Tests\Integration\App;

	use Tilwa\Testing\ModuleLevelTest;

	use Tilwa\Tests\Mocks\Interactions\ModuleThree;

	use Tilwa\Tests\Mocks\Modules\{ModuleTwo\ModuleTwoDescriptor, ModuleThree\ModuleThreeDescriptor};

	class ModuleDescriptorTest extends ModuleLevelTest {

		protected function getModules():array {

			return [$this->getModuleTwo(), $this->getModuleThree()];
		}

		private function getModuleThree ():ModuleThreeDescriptor {

			return new ModuleThreeDescriptor(new Container);
		}

		private function getModuleTwo ():ModuleTwoDescriptor {

			return (new ModuleTwoDescriptor(new Container))

			->setDependsOn([

				ModuleThree::class => $this->getModuleThree()
			]);
		}

		public function test_getDependsOn() {

			// given
			$moduleTwo = $this->getModuleTwo();

			// when
			$result = $moduleTwo->getDValueFromModuleThree();

			$moduleThree = $this->getModuleFor(ModuleThree::class);

			// then
			$this->assertSame($result, $moduleThree->getDValue());
		}
	}
?>