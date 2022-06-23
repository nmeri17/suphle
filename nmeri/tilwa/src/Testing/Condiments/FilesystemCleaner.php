<?php
	namespace Tilwa\Testing\Condiments;

	use FilesystemIterator, UnexpectedValueException;

	trait FilesystemCleaner {

		protected function assertEmptyDirectory (string $path):void {

			$this->assertTrue($this->isEmptyDirectory($path), "Failed asserting that '$path' does not exist or is empty");
		}

		protected function assertNotEmptyDirectory (string $path):void {

			$this->assertFalse($this->isEmptyDirectory($path), "Failed asserting that '$path' is not empty");
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

			$iterator = $this->safeGetIterator($path);

			return is_null($iterator) || !$iterator->valid();
		}

		private function safeGetIterator (string $path):?FilesystemIterator {

			try {

				return new FilesystemIterator($path);
			}
			catch (UnexpectedValueException $exception) { // folder does not exist

				return null;
			}
		}

		/**
		 * @see docblock on [inDirectory]
		*/
		protected function emptyDirectory (string $path):void {

			foreach ($this->safeGetIterator($path) as $childEntry) {

				$entryName = $childEntry->getPathName();

				if ($childEntry->isDir())

					$this->emptyDirectory($entryName);

				if ($childEntry->isFile()) unlink($entryName);
			}

			rmdir($path);
		}

		/**
		 * @param {indexes} Accepts wildcards
		*/
		protected function assertSavedFiles (array $indexes, TestResponseBridge $response):void {

			foreach ($indexes as $index)

				$this->assertSavedFileNames(data_get($index));
		}

		/**
		 * Deletes file after verifying its presence
		 * 
		 * @param {names} One dimensional array of literal file names
		*/
		protected function assertSavedFileNames (array $names, TestResponseBridge $response):void {

			foreach ($names as $index => $file) {

				if (is_iterable($file))

					$this->assertSavedFileNames($file, $response);

				$this->assertTrue(file_exists($file));

				unlink($file);
			}
		}
	}
?>