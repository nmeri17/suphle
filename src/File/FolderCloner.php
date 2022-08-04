<?php
	namespace Suphle\File;

	use FilesystemIterator;

	class FolderCloner {

		private $fileReplacements, $folderReplacements,

		$contentsReplacement;

		public function setEntryReplacements (
			array $fileReplacements, array $folderReplacements,

			array $contentsReplacement
		):self {

			$this->fileReplacements = $fileReplacements;

			$this->folderReplacements = $folderReplacements;

			$this->contentsReplacement = $contentsReplacement;

			return $this;
		}

		public function transferFolder (string $sourceFolder, string $newDestination):bool {

			copy( $sourceFolder, $newDestination );

			$this->walkDirectories($newDestination);

			return true;
		}

		protected function walkDirectories (string $path):void {

			$iterator = new FilesystemIterator($path);

			foreach ($iterator as $childEntry) {

				$entryName = $childEntry->getPathName();

				if ($childEntry->isDir())

					$this->walkDirectories($entryName);

				if ($childEntry->isFile()) {

					$this->replaceFileContents($entryName);

					$this->renameEntry($entryName, true);
				}
			}

			$this->renameEntry($path, false);
		}

		protected function replaceFileContents (string $fileName):void {

			$contents = file_get_contents($fileName);

			foreach ($this->contentsReplacement as $keyword => $replacement)

				$contents = str_replace($keyword, $replacement, $contents);

			file_put_contents($fileName, $contents);
		}

		protected function renameEntry (string $entryName, bool $isFile):void {

			if ($isFile)

				$keywords = $this->fileReplacements;

			else $keywords = $this->folderReplacements;

			$newName = $entryName;

			foreach ($keywords as $keyword => $replacement)

				$newName = str_replace($keyword, $replacement, $newName);

			rename($entryName, $newName);
		}
	}
?>