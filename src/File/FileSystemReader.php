<?php
	namespace Suphle\File;

	class FileSystemReader {

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
	}
?>