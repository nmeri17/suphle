<?php
	namespace Suphle\IO\Image\Operations;

	use Suphle\Contracts\IO\Image\{ImageOptimiseOperation, ImageLocator};

	use Suphle\File\FileSystemReader;

	use Symfony\Component\HttpFoundation\File\UploadedFile;

	abstract class BaseOptimizeOperation implements ImageOptimiseOperation {

		protected $files = [], $client, $imageLocator, $resourceName,

		$operationName, // using a property instead of a constant since we can't formalise such as a contract

		$fileSystemReader;

		public function __construct (ImageLocator $imageLocator, FileSystemReader $fileSystemReader) {

			$this->imageLocator = $imageLocator;

			$this->fileSystemReader = $fileSystemReader;
		}

		public function getAsyncNames ():array {

			return array_map(function ($image) {

				return $this->getImageNewName($image);
			});
		}

		protected function getImageNewName (UploadedFile $image):string {

			return $this->imageLocator->resolveName(

				$image, $this->operationName, $this->resourceName
			);
		}

		public function setFiles (array $images):void {

			$this->files = $images;
		}

		public function savesAsync ():bool {

			return false;
		}

		public function setResourceName (string $name):void {

			$this->resourceName = $name;
		}

		public function getOperationName ():string {

			return $this->operationName;
		}

		protected function localFileCopy (UploadedFile $image):string {

			$newPath = $this->getImageNewName($image);

			$this->fileSystemReader->ensureDirectoryExists($newPath);

			copy($image->getPathname(), $newPath);

			return $newPath;
		}
	}
?>