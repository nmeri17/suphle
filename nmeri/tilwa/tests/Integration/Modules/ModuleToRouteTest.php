<?php
	namespace Tilwa\Tests\Integration\Modules;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Condiments\DirectHttpTest};

	use Tilwa\Modules\ModuleToRoute;

	use Tilwa\Hydration\Container;

	use Tilwa\Tests\Mocks\Modules\{ModuleTwo\Meta\ModuleTwoDescriptor, ModuleOne\Meta\ModuleOneDescriptor};

	class ModuleToRouteTest extends ModuleLevelTest {

		use DirectHttpTest;

		protected function getModules ():array {

			return [
				new ModuleOneDescriptor(new Container),

				new ModuleTwoDescriptor(new Container)
			];
		}
		
		public function test_findContext() {

			$$sut = new ModuleToRoute($this->getModules()); // given

			$this->setHttpParams("/module-two/5"); // when

			$this->assertNotNull($sut->findContext()); // then
		}
		
		public function test_none_will_be_found() {

			$sut = new ModuleToRoute($this->getModules()); // given

			$this->setHttpParams("/non-existent/32"); // when

			$this->assertNull($sut->findContext());
		}
	}
?>