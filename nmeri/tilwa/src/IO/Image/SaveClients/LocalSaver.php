<?php
	namespace Tilwa\IO\Image\SaveClients;

	use Tilwa\Contracts\{IO\ImageSaver, Config\ModuleFiles};

	use Psr\Http\Message\UploadedFileInterface;

	class LocalSaver implements ImageSaver {

		private $storagePath;

		public function __construct (ModuleFiles $fileConfig) {

			$this->storagePath = $fileConfig->getImagePath();
		}

		protected function resolveName (UploadedFileInterface $file, string $operationName, string $resourceName):?string {

			$imageName = uniqid(). session_id() . time();

			$withExtension = $imageName. "." . $file->getClientMediaType();

			return $resourceName . DIRECTORY_SEPARATOR . $operationName . DIRECTORY_SEPARATOR . $withExtension;
		}

		public function transportImages (array $images, string $operationName, string $resourceName):array {

			foreach ($images as $image) {

				$newPath = $this->resolveName($image, $operationName, $resourceName);

				$image->moveTo($this->storagePath . DIRECTORY_SEPARATOR .$newPath);
			}
		}

		public function transportImagesAsync (array $images):void {

			foreach ($images as $newPath => $image)

				$image->moveTo($this->storagePath . DIRECTORY_SEPARATOR .$newPath);
		}

		public function savesAsync ():bool {

			return true;
		}
	}
?>