<?php
	namespace Suphle\Testing\Condiments;

	use Suphle\File\FileSystemReader;

	use Symfony\Component\HttpFoundation\File\UploadedFile;

	trait FilesystemCleaner {

		private $fileSystemReader;

		protected function assertEmptyDirectory (string $path):void {

			$this->assertTrue($this->isEmptyDirectory($path), "Failed asserting that '$path' does not exist or is empty");
		}

		protected function assertNotEmptyDirectory (string $path, bool $wipeWhenTrue = false):void {

			$this->assertFalse($this->isEmptyDirectory($path), "Failed asserting that '$path' is not empty");

			if ($wipeWhenTrue)

				$this->getFilesystemReader()->emptyDirectory($path);
		}

		/**
		 * Expects a preceding operation to have confirmed that @param {path} exists
		*/
		private function inDirectory (string $path, array $fileNames, callable $onMatchAction) {

			foreach ($fileNames as $entry) {

				$onMatchAction(
					$path, $entry,

					file_exists($path. DIRECTORY_SEPARATOR . $entry)
				);
			}
		}

		protected function assertLacksEntries (string $path, array $fileNames):void {

			if ($this->isEmptyDirectory($path)) {

				$this->assertTrue(true);

				return;
			}

			$this->inDirectory($path, $fileNames, function ($path, $givenMatch, $result) {

				$this->assertFalse($result, "Did not expect to see '$givenMatch' at $path");
			});
		}

		protected function assertContainsEntries (string $path, array $fileNames):void {

			if ($this->isEmptyDirectory($path)) {

				$this->assertTrue(false, "'$path' does not contain given entries");

				return;
			}

			$this->inDirectory($path, $fileNames, function ($path, $givenMatch, $result) {

				$this->assertTrue($result, "$path does not contain entry '$givenMatch'");
			});
		}

		private function isEmptyDirectory (string $path):bool {

			$iterator = $this->getFilesystemReader()->safeGetIterator($path);

			return is_null($iterator) || !$iterator->valid();
		}

		protected function getFilesystemReader ():FileSystemReader {

			if (is_null($this->fileSystemReader))

				$this->fileSystemReader = $this->getContainer()->getClass(FileSystemReader::class);

			return $this->fileSystemReader;
		}

		/**
		 * @param {indexes} Accepts wildcards
		 * @param {fileMap} Leaving this open to any iterable instead of tying it to TestResponseBridge to keep it agnostic to response types and more importantly, so services can be tested directly
		*/
		protected function assertSavedFiles (array $indexes, iterable $fileMap):void {

			foreach ($indexes as $index)

				$this->assertSavedFileNames(data_get($fileMap, $index));
		}

		/**
		 * Deletes file after verifying its presence
		 * 
		 * @param {files} One dimensional array of literal file names
		*/
		protected function assertSavedFileNames (iterable $files):void {

			foreach ($files as $file) {

				if (is_iterable($file))

					$this->assertSavedFileNames($file);

				$this->assertFileExists($file);

				unlink($file);
			}
		}

		/**
		 * @param {expectedSize} in kB
		 */
		protected function saveFakeImage (string $fileName, int $width, int $height, int $expectedSize = 100):UploadedFile {

			$tempImageName = $this->getImageTemporaryPath($fileName, $width, $height);

			$extension = pathinfo($fileName, PATHINFO_EXTENSION);

			while (filesize($tempImageName)/1024 < $expectedSize) {

				$loopTemp = $this->getImageTemporaryPath($fileName, $width, $height);

				file_put_contents($tempImageName, file_get_contents($loopTemp), FILE_APPEND);

				unlink($loopTemp);
			}

			return new UploadedFile($tempImageName, $fileName, $extension, null, true);
		}

		private function getImageTemporaryPath (string $extension, int $width, int $height):string {

			$extension = in_array($extension, [

				"jpeg", "png", "gif", "webp", "wbmp", "bmp"
			]) ? strtolower($extension): "jpeg";

			$imageResource = imagecreatetruecolor($width, $height);

			$writeFunction = "image$extension";

			$imagePath = $this->getTempFilePath();

			$writeFunction($imageResource, $imagePath);

			imagedestroy($imageResource);

			return $imagePath;
		}

		private function getTempFilePath ():string {

			return tempnam(sys_get_temp_dir(), "php_file");
		}

		protected function saveFakeFile (string $fileName, string $fileType, int $expectedSize = 100):UploadedFile {

			$instance = new UploadedFile($fileName, $this->getTempFilePath(), null, true);

			$instance->sizeToReport = $expectedSize * 1024;

			$instance->mimeTypeToReport = $fileType;

			return $instance;
		}
	}
?>