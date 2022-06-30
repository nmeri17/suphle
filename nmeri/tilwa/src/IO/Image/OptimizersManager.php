<?php
	namespace Tilwa\IO\Image;

	use Tilwa\Contracts\IO\Image\{ThumbnailOperationHandler, InferiorOperationHandler};

	use Tilwa\IO\Image\Jobs\AsyncImageProcessor;

	use Tilwa\Queues\AdapterManager;

	use Tilwa\Exception\Explosives\Generic\UnmodifiedImageException;

	use Symfony\Component\HttpFoundation\File\UploadedFile;

	/**
	 * Doesn't implement an interface since we don't intend to replace it. Doing so means it can be replaced with an implementation that permits save without a operation
	*/
	class OptimizersManager {

		private $operations = [], $queueManager,

		$originalImages, $thumbnailImage,

		$inferiorImage, $imageResourceName;

		public function __construct (AdapterManager $queueManager, ThumbnailOperationHandler $thumbnailImage, InferiorOperationHandler $inferiorImage) { // if the operations exceed 2, it may be more realistic to pull them from container

			$this->queueManager = $queueManager;

			$this->inferiorImage = $inferiorImage;

			$this->thumbnailImage = $thumbnailImage;
		}

		/**
		 * @return [inferior => [img1.png]]
		*/
		public function savedImageNames ():array {

			if (empty($this->operations))

				throw new UnmodifiedImageException;

			$newImageNames = [];

			foreach ($this->operations as $operation) {

				$operation->setFiles($this->originalImages);

				$operation->setResourceName ($this->imageResourceName);

				$operationName = $operation->getOperationName();

				if (!$operation->savesAsync())

					$newImageNames[$operationName] = $operation->getTransformed();

				else {

					$newImageNames[$operationName] = $operation->getAsyncNames();

					$this->queueManager->augmentArguments(
						AsyncImageProcessor::class,

						compact("operation")
					);
				}
			}

			$this->deleteTmpImages();

			return $newImageNames;
		}

		protected function deleteTmpImages ():void {

			foreach ($this->originalImages as $image)

				unlink($image->getPathname());
		}

		/**
		 * @param {images} UploadedFile[]
		*/
		public function setImages (array $images, string $resourceName):self {

			$this->originalImages = $images;

			$this->imageResourceName = $resourceName;

			return $this;
		}

		public function inferior (int $maxSize):self {

			$this->inferiorImage->setMaxSize($maxSize);

			$this->operations[] = $this->inferiorImage;

			return $this;
		}

		public function thumbnail (int $width, int $height):self {

			$this->thumbnailImage->setDimensions($width, $height);

			$this->operations[] = $this->thumbnailImage;

			return $this;
		}
	}
?>