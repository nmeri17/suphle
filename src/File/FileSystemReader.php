<?php
	namespace Suphle\File;

	use FilesystemIterator, Throwable;

	class FileSystemReader {

		protected array $filesFinalDestination = [];

		/**
		 * Traverse {currentWorkingDirectory} backwards, for the number of levels given by dots in {relativePath}
		 * 
		 * @param {relativePath}: Expects a location relative to given absolute path 
		 * @param {currentWorkingDirectory}: Absolute path to use as anchor for the operation
		 * @return Normalized path to {relativePath} without trailing slash
		*/
		public function getAbsolutePath (string $currentWorkingDirectory, string $relativePath):string {

			$allSegments = explode("../", $relativePath);

			return $this->pathFromLevels($currentWorkingDirectory,

				end($allSegments), count($allSegments)-1
			);
		}

		public function getFileName (string $filePath):string {

			preg_match("/([\w-]+\.\w+)$/", $filePath, $matches);

			return $matches[1];
		}

		/**
		 * Same as [getAbsolutePath], but the levels are given beforehand instead of being calculated
		*/
		public function pathFromLevels (string $currentWorkingDirectory, string $intendedPath, int $upLevels):string {

			if ($upLevels > 0)

				$absoluteDirectory = dirname($currentWorkingDirectory, $upLevels); // since dirname already goes one level up

			else $absoluteDirectory = $currentWorkingDirectory;

			return $absoluteDirectory . DIRECTORY_SEPARATOR .

			$intendedPath;
		}

		public function ensureDirectoryExists (string $fullPath, bool $isFile = true):void {

			if ($isFile) $newFolder = dirname($fullPath);

			else $newFolder = $fullPath;

			if (!file_exists($newFolder))

				mkdir($newFolder, 0755, true); // 3rd argument = create parents if they don't exist
		}

		/**
		 * Use for more robust handling of paths that can come from different sources
		*/
		public function noTrailingSlash (string $path):string {

			preg_match("/(.+?)[\\/\\\\]*$/", $path, $matches); // actually => \/\\ i.e. any back or forward slash

			return $matches[1];
		}

		public function lastCopiedBatch ():array {

			return $this->filesFinalDestination;
		}

		public function resetCopiedBatch ():void {

			$this->filesFinalDestination = [];
		}

		/**
		 * @param {onDirectory} has to be recursive for this method to function as expected
		*/
		public function iterateDirectory (

			string $path, callable $onDirectory, callable $onFile,

			callable $onCompletion = null
		):void {

			foreach (new FilesystemIterator($path) as $childEntry) {

				$fullPath = $childEntry->getPathName();

				$entryName = $childEntry->getBaseName();

				if ($childEntry->isDir())

					$onDirectory($fullPath, $entryName);

				if ($childEntry->isFile())

					$onFile($fullPath, $entryName);
			}

			if (!is_null($onCompletion))

				$onCompletion($path);
		}

		public function deepCopy (string $sourceFolder, string $currentDestination):void {

			$currentDestination = $this->noTrailingSlash($currentDestination);

			$this->iterateDirectory(

				$sourceFolder,

				function ($sourcePath, $sourceName) use ($currentDestination) {

					$newDestination = $currentDestination . DIRECTORY_SEPARATOR . $sourceName;

					$this->ensureDirectoryExists($newDestination, false);

					$this->deepCopy($sourcePath, $newDestination);
				},
				function ($filePath, $fileName) use ($currentDestination) {

					$newDestination = $currentDestination . DIRECTORY_SEPARATOR . $fileName; // in a folder containing folders and files, the files will be read first, which means destination path is expected to exist otherwise copy won't work

					$this->ensureDirectoryExists($newDestination, true);

					$this->filesFinalDestination[] = $newDestination;

					copy($filePath, $newDestination);
				}
			);
		}

		public function emptyDirectory (string $path):void {

			$this->iterateDirectory(

				$path, function ($directoryPath, $directoryName) {

					$this->emptyDirectory($directoryPath);
				},

				function ($fullPath, $fileName) {

					unlink($fullPath);
				},

				function ($fullPath) {

					$this->safeDeleteDirectory($fullPath);
				}
			);
		}

		protected function safeDeleteDirectory (string $directoryPath):void {

			try {

				if (file_exists($directoryPath)) // it's possible for it to have been renamed or deleted by a preceding operation

					rmdir($directoryPath);

				else trigger_error("Attempt to delete non-existent folder: $directoryPath", E_USER_WARNING);
			}
			catch (Throwable $exception) {

				if (stripos($exception->getMessage(), "directory not empty") === false)

					throw $exception;

				// var_dump(151, "retrying folder delete $directoryPath");

				$this->emptyDirectory($directoryPath); // maybe names have been changed. Keep emptying until path doesn't exist
			}
		}
	}
?>