<?php
	namespace Suphle\Tests\Integration\Production;

	use Suphle\Server\VendorBin;

	use Suphle\File\FileSystemReader;

	use Suphle\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

	abstract class BaseTestProduction extends DescriptorCollection {

		protected bool $debugCaughtExceptions = true,
  
  		$useTestComponents = false;

		protected VendorBin $vendorBin;

		protected function setUp ():void {

			parent::setUp();

			$container = $this->getContainer();

			$this->vendorBin = $container->getClass(VendorBin::class);

			$this->vendorBin->setRootPath(

				$container->getClass(FileSystemReader::class)

				->pathFromLevels($_SERVER["COMPOSER_RUNTIME_BIN_DIR"], "", 2)
			);
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