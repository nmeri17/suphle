<?php
	namespace Suphle\File;

	class FileSystemReader {

		/**
		 * Traverse {currentWorkingDirectory} backwards, for the number of levels given by dots in {relativePath}
		 * 
		 * @param {relativePath}: Expects a location relative to given absolute path 
		 * @param {currentWorkingDirectory}: Absolute path to use as anchor for the operation
		 * @return Normalized path to {relativePath}
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

		public function ensureDirectoryExists (string $fullPath):void {

			$newFolder = dirname($fullPath);

			if (!file_exists($newFolder))

				mkdir($newFolder, 0777, true); // 3rd argument = create parents if they don't exist
		}

		/**
		 * Use for more robust handling of paths that can come from different sources
		*/
		public function noTrailingSlash (string $path):string {

			preg_match("/(.+?)[\/\\]*$/", $path, $matches);

			return $matches[1];
		}
	}
?>