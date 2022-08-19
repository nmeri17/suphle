<?php
	namespace Suphle\IO\Image\Operations;

	use Suphle\Contracts\IO\Image\{ InferiorImageClient, ImageLocator, InferiorOperationHandler};

	use Suphle\File\FileSystemReader;

	class DefaultInferiorHandler extends BaseOptimizeOperation implements InferiorOperationHandler {

		protected $operationName = InferiorOperationHandler::OPERATION_NAME;

		private $maxSize;

		public function __construct (InferiorImageClient $client, ImageLocator $imageLocator, FileSystemReader $fileSystemReader) {

			$this->client = $client;

			parent::__construct($imageLocator, $fileSystemReader);
		}

		public function setMaxSize (int $size):void {

			$this->maxSize = $size;
		}

		public function getTransformed ():array {

			return array_map(function ($file) {

				return $this->client->downgradeImage(
					
					$file, $this->localFileCopy($file), $this->maxSize
				);
			}, $this->files);
		}
	}
?>