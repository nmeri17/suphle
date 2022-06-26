<?php
	namespace Tilwa\IO\Image\Operations;

	use Tilwa\Contracts\IO\Image\{ ImageThumbnailClient, ImageLocator, ThumbnailOperationHandler};

	class DefaultThumbnailHandler extends BaseOptimizeOperation implements ThumbnailOperationHandler {

		protected $operationName = ThumbnailOperationHandler::OPERATION_NAME;

		public function __construct (ImageThumbnailClient $client, ImageLocator $imageLocator) {

			$this->client = $client;

			$this->imageLocator = $imageLocator;
		}

		public function getTransformed ():array {

			return array_map(function ($file) {

				return $this->client->miniature(
					$file->getPathname(),

					$this->imageLocator->resolveName(

						$file, $this->operationName, $this->resourceName
					)
				);
			}, $this->files);
		}

		public function setDimensions(int $width, int $height):void {

			$this->client->setDimensions($width, $height);
		}
	}
?>