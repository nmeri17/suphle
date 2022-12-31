<?php
	namespace Suphle\IO\Image\Operations;

	use Suphle\Contracts\IO\Image\{ImageOptimiseOperation, ImageLocator};

	use Suphle\File\FileSystemReader;

	use Symfony\Component\HttpFoundation\File\UploadedFile;

	abstract class BaseOptimizeOperation implements ImageOptimiseOperation {

		protected array $imageObjects = [], $generatedFileNames = []; // only required for async operations
		protected $client;
		
		protected string $resourceName, $operationName; // using a property instead of a constant since we can't formalise such as a contract

		public function __construct(protected ImageLocator $imageLocator, protected FileSystemReader $fileSystemReader) {

			//
		}

		public function getAsyncNames ():array {

			return $this->generatedFileNames = array_map(fn($image) => $this->getImageNewName($image), $this->imageObjects);
		}

		protected function getImageNewName (UploadedFile $image):string {

			return $this->imageLocator->resolveName(

				$image, $this->operationName, $this->resourceName
			);
		}

		public function setFiles (array $images):void {

			$this->imageObjects = $images;
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