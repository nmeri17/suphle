<?php
	namespace Tilwa\Adapters\Image\Optimizers;

	use Tilwa\Contracts\IO\Image\ImageThumbnailClient;

	use Imagine\Gd\Imagine;

	use Imagine\Image\{ImageInterface, Box};

	class ImagineClient implements ImageThumbnailClient {

		private $context, $mode, $size;

		public function setupClient ():void {

			$this->context = new Imagine();

			$this->mode = ImageInterface::THUMBNAIL_INSET;
		}

		public function setDimensions (int $width, int $height):void {

			$this->size = new Box($width, $height);
		}

		/**
		 * {@inheritdoc}
		*/
		public function miniature (string $imageNewPath):string {

			$this->context->open($imageNewPath)

			->thumbnail($this->size, $this->mode)

			->save($imageNewPath );

			return $imageNewPath;
		}
	}
?>