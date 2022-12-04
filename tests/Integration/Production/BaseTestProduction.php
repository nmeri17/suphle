<?php
	namespace Suphle\Tests\Integration\Production;

	use Suphle\Server\VendorBin;

	use Suphle\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

	abstract class BaseTestProduction extends DescriptorCollection {

		protected bool $debugCaughtExceptions = true;
  protected $useTestComponents = false;

		protected VendorBin $vendorBin;

		protected function setUp ():void {

			parent::setUp();

			$this->vendorBin = $this->getContainer()

			->getClass(VendorBin::class);
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