<?php
	namespace Tilwa\Tests\Integration\Files;

	use Tilwa\File\FileSystemReader;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	class FileSystemReaderTest extends IsolatedComponentTest {

		use CommonBinds;

		private $sut, $filePath = "Sibling/File.txt";

		protected function setUp ():void {

			parent::setUp();

			$this->sut = new FileSystemReader;
		}

		public function test_normalizes_path_with_up_above_one () {

			$this->assertSame(

				dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $this->filePath,

				$this->sut->getAbsolutePath(__DIR__, "../../" . $this->filePath)
			);
		}

		public function test_normalizes_path_with_one_up () {

			$this->assertSame(

				dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . $this->filePath,

				$this->sut->getAbsolutePath(__DIR__, "../" . $this->filePath) // integration/filePath
			);
		}

		public function test_normalizes_path_without_up () {

			$this->assertSame(

				__DIR__ . DIRECTORY_SEPARATOR . $this->filePath,

				$this->sut->getAbsolutePath(__DIR__, $this->filePath)
			);
		}
	}
?>