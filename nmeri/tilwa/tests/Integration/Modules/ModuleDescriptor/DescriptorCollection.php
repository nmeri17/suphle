<?php
	namespace Tilwa\Tests\Integration\Modules\ModuleDescriptor;

	use Tilwa\Testing\TestTypes\ModuleLevelTest;

	use Tilwa\Tests\Integration\Generic\TestsModuleList;

	class DescriptorCollection extends ModuleLevelTest {

		use TestsModuleList;

		protected function setUp ():void {

			$this->setAllDescriptors();

			parent::setUp();
		}

		protected function getModules():array {

			return $this->getAllDescriptors();
		}
	}
?>