<?php
	namespace Suphle\IO\Image\SaveClients;

	use Suphle\Contracts\{IO\Image\ImageLocator, Config\ModuleFiles};

	use Symfony\Component\HttpFoundation\File\UploadedFile;

	class LocalSaver implements ImageLocator {

		private $storagePath;

		public function __construct (ModuleFiles $fileConfig) {

			$this->storagePath = $fileConfig->getImagePath();
		}

		public function resolveName (
			UploadedFile $file, string $operationName,

			string $resourceName
		):string {

			$imageName = uniqid(). session_id() . time();

			$withExtension = $imageName. "." . $file->guessExtension();

			return $this->storagePath . implode(DIRECTORY_SEPARATOR, [

				$resourceName, $operationName, $withExtension
			]);
		}
	}
?>