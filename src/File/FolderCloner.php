<?php
	namespace Suphle\File;

	use FilesystemIterator, Throwable, Exception;

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

			$temporaryModulePath = dirname($sourceFolder). DIRECTORY_SEPARATOR . "temp_dir"; // using this so we don't iterate existing files at that destination

			$this->fileSystemReader->deepCopy( $sourceFolder, $temporaryModulePath );

			$this->nameContentChange($temporaryModulePath);

			$this->renameEntryOnDisk($temporaryModulePath, $newDestination); // since we copied instead of moved, we have do the actual moving from temp to permanent

			return true;
		}

		protected function nameContentChange (string $path):void {

			$this->fileSystemReader->iterateDirectory(

				$path, function ($directoryPath, $directoryName) {

					$this->nameContentChange($directoryPath);
				},

				function ($filePath, $fileName) {

					$this->replaceFileContents($filePath);

					$this->setEntryName($filePath, true);
				},

				function ($path) {

					$this->setEntryName($path, false);
				}
			);
		}

		protected function replaceFileContents (string $fileName):void {

			$contents = file_get_contents($fileName);

			foreach ($this->contentsReplacement as $keyword => $replacement)

				$contents = str_replace($keyword, $replacement, $contents);

			file_put_contents($fileName, $contents);
		}

		/**
		 * Builds the new name for the entry and renames it at the temporary location*/
		protected function setEntryName (string $entryPath, bool $isFile):void {

			if ($isFile) $keywords = $this->fileReplacements;

			else $keywords = $this->folderReplacements;

			$newName = basename($entryPath); // change only this entry since the preceding/parent paths haven't been renamed on disk yet/don't exist

			foreach ($keywords as $keyword => $replacement)

				$newName = str_replace($keyword, $replacement, $newName);
			
			$this->renameEntryOnDisk(

				$entryPath,

				dirname($entryPath) . DIRECTORY_SEPARATOR .$newName
			);
		}

		protected function renameEntryOnDisk (string $sourceFolder, string $newDestination):void {

			$operationSuccess = false;

			try {

				if (file_exists($sourceFolder)) {

					if (rename($sourceFolder, $newDestination)) // We don't want to rely on the invocation of NativeErrorHandlers::silentErrorToException. Unsuccessful rename shouldn't pretend to have succeeded to the caller

						$operationSuccess = true;
				}

				else trigger_error("Attempt to rename non-existent folder: $sourceFolder");
			}
			catch (Throwable $exception) {

				if (stripos($exception->getMessage(), "access is denied") === false) // throw all non-permission related issues. This happens since we didn't create that folder. Trying to remove it causes system to revolt. So, we do it manually

					throw $exception;

				$this->fileSystemReader->deepCopy($sourceFolder, $newDestination);
				
				$this->fileSystemReader->emptyDirectory($sourceFolder);

				$operationSuccess = true;
			}

			if (!$operationSuccess)

				throw new Exception("Unable to rename folder: $sourceFolder");
		}
	}
?>