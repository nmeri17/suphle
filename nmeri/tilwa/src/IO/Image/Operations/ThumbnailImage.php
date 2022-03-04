<?php
	namespace Tilwa\IO\Image\Operations;

	use Tilwa\Contracts\IO\{ ImageThumbnailContract, ImageLocator};

	class ThumbnailImage extends BaseOptimizeOperation {

		private $width, $height;

		public function __construct (ImageThumbnailContract $client, ImageLocator $imageLocator) {

			$this->client = $client;

			$this->imageLocator = $imageLocator;
		}

		public function getTransformed ():array {

			$savedNames = [];

			$this->client->setDimensions($this->width, $this->height);

			foreach ($this->files as $image)

				$savedNames[] = $this->client->miniature(
					$this->imageLocator->temporarilyRelocate($file), // for us to get a file name

					$this->imageLocator->resolveName($file, $this->operationName, $this->resourceName)
				);

			return $savedNames;
		}

		public function setDimensions(int $width, int $height):void {

			$this->width = $width;

			$this->height = $height;
		}
	}
?>