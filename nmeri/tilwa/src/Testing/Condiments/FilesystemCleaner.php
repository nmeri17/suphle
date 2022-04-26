<?php
	namespace Tilwa\Testing\Condiments;

	use FilesystemIterator, UnexpectedValueException;

	trait FilesystemCleaner {

		private $currentIterator;

		protected function assertEmptyDirectory (string $path):void {

			$this->assertTrue($this->isEmptyDirectory($path), "Failed asserting that '$path' does not exist or is empty");
		}

		protected function assertNotEmptyDirectory (string $path):void {

			$this->assertFalse($this->isEmptyDirectory($path), "Failed asserting that '$path' is not empty");
		}

		private function inDirectory (string $path, array $fileNames, callable $onMatchAction) {

			foreach ($this->currentIterator as $childEntry) {

				if ($childEntry->isDot()) continue;

				foreach ($fileNames as $suspect) {

					$sanitizedPath = str_replace("/", "\/", $suspect);

					$onMatchAction(
						$path, $suspect,

						preg_match("/$sanitizedPath/", $childEntry->getFilename())
					);
				}
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

			try {

				$this->currentIterator = new FilesystemIterator($path);

				return !$this->currentIterator->valid();
			}
			catch (UnexpectedValueException $exception) { // folder does not exist

				return true;
			}
		}

		protected function emptyDirectory (string $path):void {

			foreach ($this->currentIterator as $childEntry) {

				$entryName = $childEntry->getPathName();

				if ($childEntry->isDir())

					$this->emptyDirectory($entryName);

				if ($childEntry->isFile()) unlink($entryName);
			}

			rmdir($path);
		}
	}
?>