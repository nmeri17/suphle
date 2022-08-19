<?php
	namespace Suphle\Tests\Integration\Files;

	use Suphle\File\FileSystemReader;

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Tests\Integration\Generic\CommonBinds;

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

		/**
		 * @dataProvider slashedAndUnslashed
		*/
		public function test_slash_trimming (string $givenPath, string $expectedPath) {

			$this->assertSame($expectedPath, $this->sut->noTrailingSlash($givenPath));
		}

		public function slashedAndUnslashed ():array {

			return [
				["/foo/bar", "/foo/bar"],

				["/foo/bar/", "/foo/bar"],

				["\\foo\\bar\\", "\\foo\\bar"],
			];
		}
	}
?>