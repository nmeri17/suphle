<?php
	namespace Tilwa\IO\Image\Operations;

	use Tilwa\Contracts\IO\Image\{ InferiorImageClient, ImageLocator, InferiorOperationHandler};

	class DefaultInferiorHandler extends BaseOptimizeOperation implements InferiorOperationHandler {

		private $maxSize;

		public function __construct (InferiorImageClient $client, ImageLocator $imageLocator) {

			$this->client = $client;

			$this->imageLocator = $imageLocator;
		}

		public function setMaxSize (int $size):void {

			$this->maxSize = $size;
		}

		public function getTransformed ():array {

			$savedNames = [];

			foreach ($this->files as $image) {

				if ($image->getSize() >= $this->maxSize)

					$this->client->downgrade($this->imageLocator->temporarilyRelocate($image)); // using a dummy path since we have no way to determine and provide a path to this client. E.g. url => images/dummies/cat.png

				$savedNames[] = $this->client->moveDowngraded(
					$image,

					$this->imageLocator->resolveName($image, $this->operationName, $this->resourceName)
				);
			}

			return $savedNames;
		}
	}
?>