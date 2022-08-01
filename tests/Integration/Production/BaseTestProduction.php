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

			return [

				[
					"/module-three/4", json_encode([])
				], [
					"/segment", json_encode([

						"message" => "plain Segment"
					])
				]
			];
		}
	}
?>