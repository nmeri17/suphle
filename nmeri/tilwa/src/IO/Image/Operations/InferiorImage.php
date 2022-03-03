<?php
	namespace Tilwa\IO\Image\Operations;

	use Tilwa\Contracts\IO\ImageOptimiseOperation;

	class InferiorImage implements ImageOptimiseOperation {

		private $files, $client, $maxSize;

		public function __construct (InferiorImageClient $client) {

			$this->client = $client;
		}

		public function setMaxSize (int $size) {

			$this->maxSize = $size;
		}

		public function getTransformed ():array {

			//
		}

		public function setFiles (array $images):void {

			$this->files = $images;
		}
	}
?>