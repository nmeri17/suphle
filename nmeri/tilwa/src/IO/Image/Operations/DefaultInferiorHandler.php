<?php
	namespace Tilwa\IO\Image\Operations;

	use Tilwa\Contracts\IO\Image\{ InferiorImageClient, ImageLocator, InferiorOperationHandler};

	class DefaultInferiorHandler extends BaseOptimizeOperation implements InferiorOperationHandler {

		protected $operationName = InferiorOperationHandler::OPERATION_NAME;

		private $maxSize;

		public function __construct (InferiorImageClient $client, ImageLocator $imageLocator) {

			$this->client = $client;

			$this->imageLocator = $imageLocator;
		}

		public function setMaxSize (int $size):void {

			$this->maxSize = $size;
		}

		public function getTransformed ():array {

			return array_map(function ($file) {

				if ($file->getSize() >= $this->maxSize)

					$this->client->downgrade($file->getPathname()); // assumes no prior operation has relocated file

				return $this->client->moveDowngraded(
					$file,

					$this->imageLocator->resolveName(

						$file, $this->operationName, $this->resourceName
					)
				);
			}, $this->files);
		}
	}
?>