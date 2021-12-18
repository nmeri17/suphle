<?php
	namespace Tilwa\Tests\Integration\App;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Condiments\DirectHttpTest};

	use Tilwa\App\{ModuleToRoute, Container};

	use Tilwa\Tests\Mocks\Modules\{ModuleTwo\ModuleTwoDescriptor, ModuleOne\ModuleOneDescriptor};

	class ModuleToRouteTest extends ModuleLevelTest {

		use DirectHttpTest;

		private function getModules ():array {

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