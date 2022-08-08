<?php
	namespace Suphle\Tests\Integration\Production;

	use Suphle\File\FileSystemReader;

	use Suphle\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

	abstract class BaseTestProduction extends DescriptorCollection {

		protected $debugCaughtExceptions = true,

		$useTestComponents = false, $binDir, $fileSystemReader;

		protected function setUp ():void {

			parent::setUp();

			$this->binDir = $_SERVER["COMPOSER_RUNTIME_BIN_DIR"];

			$this->fileSystemReader = new FileSystemReader;
		}

		public function modulesUrls ():array {

			return [ $this->moduleThreeUrls()[0], [
					"/segment", json_encode([

						"message" => "plain Segment"
					])
				]
			];
		}

		public function moduleThreeUrls ():array {

			return [

				[
					"/module-three/4", json_encode([

						"id" => 4
					])
				], [
					"/module-three/8", json_encode([

						"id" => 8
					])
				]
			];
		}
	}
?>