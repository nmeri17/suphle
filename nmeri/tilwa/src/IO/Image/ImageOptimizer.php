<?php
	namespace Tilwa\IO\Image;

	use Tilwa\IO\Image\Operations\{ThumbnailImage, InferiorImage};

	use Tilwa\IO\Image\Jobs\AsyncImageProcessor;

	use Tilwa\Contracts\Services\Decorators\OnlyLoadedBy;

	use Tilwa\Services\ServiceCoordinator;

	use Tilwa\Queues\AdapterManager;

	use Tilwa\Exception\Explosives\Generic\UnmodifiedImageException;

	class ImageOptimizer implements OnlyLoadedBy {

		private $operations = [], $queueManager,

		$originalImages, $thumbnailImage,

		$inferiorImage, $imageResourceName;

		public function __construct (AdapterManager $queueManager, ThumbnailImage $thumbnailImage, InferiorImage $inferiorImage) {

			$this->queueManager = $queueManager;

			$this->inferiorImage = $inferiorImage;

			$this->thumbnailImage = $thumbnailImage;
		}

		final public function allowedConsumers ():array {

			return [ServiceCoordinator::class];
		}

		/**
		 * @return [inferior => [img1.png]]
		*/
		public function getNames ():array {

			if (empty($this->operations))

				throw new UnmodifiedImageException;

			$newImageNames = [];

			foreach ($this->operations as $operationName => $operation) {

				$operation->setFiles($this->originalImages);

				$operation->setResourceName ($this->imageResourceName);

				$operation->setName($operationName);

				if (!$operation->savesAsync())

					$newImageNames[$operationName] = $operation->getTransformed();

				else {

					$newImageNames[$operationName] = $operation->getAsyncNames();

					$this->queueManager->augmentArguments(
						AsyncImageProcessor::class,

						compact("operation")
					);
				}

				return $newImageNames;
			}
		}

		/**
		 * @param {images} UploadedFileInterface[]
		*/
		public function setImages (array $images, string $resourceName):self {

			$this->originalImages = $images;

			$this->imageResourceName = $resourceName;

			return $this;
		}

		public function inferior (int $maxSize):self {

			$this->inferiorImage->setMaxSize($maxSize);

			$this->operations[__FUNCTION__] = $this->inferiorImage;

			return $this;
		}

		public function thumbnail (int $width, int $height):self {

			$this->thumbnailImage->setDimensions($width, $height);

			$this->operations[__FUNCTION__] = $this->thumbnailImage;

			return $this;
		}
	}
?>