<?php
	namespace Suphle\Tests\Integration\Modules;

	use Suphle\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

	class Module3Test extends DescriptorCollection {

		protected $debugCaughtExceptions = true,

		$useTestComponents = false;

		protected function getModules():array {

			return [

				$this->moduleThree
			];
		}
		
		public function test_can_handle_login () {

			$this->get("/module-three/4")->assertOk();
		}
	}
?>