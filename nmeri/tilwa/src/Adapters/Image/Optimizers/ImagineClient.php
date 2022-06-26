<?php
	namespace Tilwa\Adapters\Image\Optimizers;

	use Tilwa\Contracts\IO\Image\ImageThumbnailClient;

	use Tilwa\File\FileSystemReader;

	use Imagine\Gd\Imagine;

	use Imagine\Image\{ImageInterface, Box};

	class ImagineClient implements ImageThumbnailClient {

		private $context, $mode, $size, $fileSystemReader;

		public function __construct (FileSystemReader $fileSystemReader) {

			$this->fileSystemReader = $fileSystemReader;
		}

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
		public function miniature (string $oldPath, ?string $newPath):string {

			$this->fileSystemReader->ensureDirectoryExists($newPath);

			$this->context->open($oldPath)

			->thumbnail($this->size, $this->mode)

			->save($newPath );

			return $newPath;
		}
	}
?>