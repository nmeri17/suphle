<?php
	namespace Tilwa\Tests\Integration\Modules;

	use Tilwa\Modules\ModuleToRoute;

	use Tilwa\Hydration\Container;

	use Tilwa\Testing\Condiments\DirectHttpTest;

	use Tilwa\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

	class ModuleToRouteTest extends DescriptorCollection {

		use DirectHttpTest;

		private $sut;

		protected function setUp ():void {

			parent::setUp();

			$this->prepareAllModules();

			$this->sut = $this->getContainer()->getClass(ModuleToRoute::class);
		}

		protected function getModules ():array {

			return [ $this->moduleOne, $this->moduleTwo ];
		}
		
		public function test_findContext() {

			$this->setHttpParams("/module-two/5"); // when

			$this->assertNotNull($this->sut->findContext(

				$this->modules // given

			)); // then
		}
		
		public function test_none_will_be_found() {

			$this->setHttpParams("/non-existent/32"); // when

			$this->assertNull($this->sut->findContext(

				$this->modules // given

			)); // then
		}
	}
?>