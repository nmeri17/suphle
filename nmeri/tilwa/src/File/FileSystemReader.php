<?php
	namespace Tilwa\File;

	class FileSystemReader {

		public function getAbsolutePath (string $currentWorkingDirectory, string $relativePath):string {

			$allSegments = explode("../", $relativePath);

			return $this->pathFromLevels($currentWorkingDirectory,

				end($allSegments), count($allSegments)
			);
		}

		public function getFileName (string $filePath):string {

			preg_match("/([\w-]+\.\w+)$/", $filePath, $matches);

			return $matches[1];
		}

		public function pathFromLevels (string $currentWorkingDirectory, string $intendedPath, int $upLevels):string {

			if ($upLevels > 1)

				$absoluteDirectory = dirname($currentWorkingDirectory, $upLevels-1); // since dirname already goes one level up

			else $absoluteDirectory = $currentWorkingDirectory;

			return $absoluteDirectory . DIRECTORY_SEPARATOR .

			$intendedPath;
		}
	}
?>