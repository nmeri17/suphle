<?php
	namespace Suphle\Tests\Integration\Production;

	use Suphle\Testing\Proxies\RealVendorPath;

	use Suphle\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

	abstract class BaseTestProduction extends DescriptorCollection {

		use RealVendorPath;

		protected bool $debugCaughtExceptions = true,
  
  		$useTestComponents = false;

		protected function setUp ():void {

			parent::setUp();

			$this->setVendorPath();
		}

		public function modulesUrls ():array {

			return [
				$this->moduleThreeUrls()[0],
				[
					"segment", json_encode([

						"message" => "plain Segment"
					])
				]
			];
		}

		public function moduleThreeUrls ():array {

			return [

				[
					"module-three/4", json_encode([

						"id" => 4
					])
				], [
					"module-three/8", json_encode([

						"id" => 8
					])
				]
			];
		}
	}
?>