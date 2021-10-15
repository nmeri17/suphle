<?php
	namespace Tilwa\Tests\Integration\App;

	use Tilwa\{Testing\IsolatedComponentTest, App\ModuleToRoute};

	use Tilwa\Tests\Mocks\Modules\{ModuleTwo\ModuleTwoDescriptor, ModuleOne\ModuleOneDescriptor};

	class ModuleToRouteTest extends IsolatedComponentTest {
		
		public function test_findContext() {

			$modules = [
				ModuleOneDescriptor::class, ModuleTwoDescriptor::class
			];

			$sut = new ModuleToRoute($modules); // given

			$this->setHttpParams("/module-two/5"); // when

			$this->assertNotNull($sut->findContext()); // then
		}
		
		public function test_none_will_be_found() {

			$modules = [
				ModuleOneDescriptor::class, ModuleTwoDescriptor::class
			];

			$sut = new ModuleToRoute($modules); // given

			$this->setHttpParams("/non-existent/32"); // when

			$this->assertNull($sut->findContext());
		}
	}
?>