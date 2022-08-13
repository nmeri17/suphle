<?php
	namespace Suphle\File;

	use FilesystemIterator, UnexpectedValueException;

	class FileSystemReader {

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

				mkdir($newFolder, 0777, true); // 3rd argument = create parents if they don't exist
		}

		/**
		 * Use for more robust handling of paths that can come from different sources
		*/
		public function noTrailingSlash (string $path):string {

			preg_match("/(.+?)[\\/\\\\]*$/", $path, $matches); // actually => \/\\ i.e. any back or forward slash

			return $matches[1];
		}

		public function safeGetIterator (string $path):?FilesystemIterator {

			try {

				return new FilesystemIterator($path);
			}
			catch (UnexpectedValueException $exception) { // folder does not exist

				return null;
			}
		}

		public function iterateDirectory (

			string $path, callable $onDirectory, callable $onFile,

			callable $onCompletion = null
		):void {

			foreach ($this->safeGetIterator($path) as $childEntry) {

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

			$this->iterateDirectory(

				$sourceFolder,

				function ($sourcePath, $sourceName) use ($currentDestination) {

					$newDestination = $currentDestination . DIRECTORY_SEPARATOR . $sourceName;

					$this->ensureDirectoryExists($newDestination, false);

					$this->deepCopy($sourcePath, $newDestination); // correct other usages
				},
				function ($filePath, $fileName) use ($currentDestination) {

					copy(
						$filePath,

						$currentDestination . DIRECTORY_SEPARATOR . $fileName
					);
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

				"rmdir"
			);
		}
	}
?>