<?php
	namespace Tilwa\IO\Image\SaveClients;

	use Tilwa\Contracts\{IO\ImageLocator, Config\ModuleFiles};

	use Psr\Http\Message\UploadedFileInterface;

	class LocalSaver implements ImageLocator {

		private $storagePath;

		protected $dummyFolder = "dummies";

		public function __construct (ModuleFiles $fileConfig) {

			$this->storagePath = $fileConfig->getImagePath();
		}

		protected function resolveName (UploadedFileInterface $file, string $operationName, string $resourceName):?string {

			$imageName = uniqid(). session_id() . time();

			$withExtension = $imageName. "." . $file->guessClientExtension();

			$segments = [

				$this->storagePath, $resourceName, $operationName,

				$withExtension
			];

			return implode(DIRECTORY_SEPARATOR, $segments);
		}

		public function temporarilyRelocate (UploadedFileInterface $image):string {

			$dummyPath = $this->storagePath . DIRECTORY_SEPARATOR .

			$this->dummyFolder . DIRECTORY_SEPARATOR .

			$image->getClientFilename();

			$image->moveTo($dummyPath);

			return $dummyPath;
		}

		
	}
?>