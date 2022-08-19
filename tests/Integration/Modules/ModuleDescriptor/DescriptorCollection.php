<?php
	namespace Suphle\Tests\Integration\Modules\ModuleDescriptor;

	use Suphle\Testing\TestTypes\ModuleLevelTest;

	use Suphle\Tests\Integration\Generic\TestsModuleList;

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