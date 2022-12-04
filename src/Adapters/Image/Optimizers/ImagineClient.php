<?php
	namespace Suphle\Adapters\Image\Optimizers;

	use Suphle\Contracts\IO\Image\ImageThumbnailClient;

	use Imagine\Gd\Imagine;

	use Imagine\Image\{ImageInterface, Box};

	class ImagineClient implements ImageThumbnailClient {

		private Imagine $context;

		private $mode;

		private $size;

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