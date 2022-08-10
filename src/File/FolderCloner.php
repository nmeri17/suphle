<?php
	namespace Suphle\File;

	use FilesystemIterator;

	class FolderCloner {

		private $fileReplacements, $folderReplacements,

		$contentsReplacement, $fileSystemReader;

		public function __construct (FileSystemReader $fileSystemReader) {

			$this->fileSystemReader = $fileSystemReader;
		}

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

			$this->fileSystemReader->deepCopy( $sourceFolder, $newDestination );

			$this->nameContentChange($newDestination);

			return true;
		}

		protected function nameContentChange (string $path):void {

			$this->fileSystemReader->iterateDirectory(

				$path, function ($directoryPath, $directoryName) {

					$this->nameContentChange($directoryPath);
				},

				function ($filePath, $fileName) {

					$this->replaceFileContents($filePath);

					$this->renameEntry($filePath, true);
				},

				function ($path) {

					$this->renameEntry($path, false);
				}
			);
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