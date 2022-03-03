<?php
	namespace Tilwa\IO\Image\Operations;

	use Tilwa\Contracts\IO\ImageOptimiseOperation;

	class ThumbnailImage implements ImageOptimiseOperation {

		private $files, $client, $width, $height;

		public function __construct (ImageThumbnailClient $client) {

			$this->client = $client;
		}

		public function getTransformed ():array {

			//
		}

		public function setDimensions(int $width, int $height) {

			$this->width = $width;

			$this->height = $height;
		}

		public function setFiles (array $images):void {

			$this->files = $images;
		}
	}
?>