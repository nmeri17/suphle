<?php
	namespace Tilwa\IO\Image;

	use Tilwa\IO\Image\Operations\{ThumbnailImage, InferiorImage};

	use Tilwa\IO\Image\Jobs\AsyncImageProcessor;

	use Tilwa\Contracts\{Services\Decorators\OnlyLoadedBy, IO\ImageSaver};

	use Tilwa\Services\ServiceCoordinator;

	use Tilwa\Queues\AdapterManager;

	use Tilwa\Exception\Explosives\Generic\UnmodifiedImageException;

	class ImageOptimizer implements OnlyLoadedBy {

		private $operations = [], $queueManager,

		$imageSaver, $originalImages, $thumbnailImage,

		$inferiorImage, $imageResourceName;

		public function __construct (AdapterManager $queueManager, ImageSaver $imageSaver, ThumbnailImage $thumbnailImage, InferiorImage $inferiorImage) {

			$this->queueManager = $queueManager;

			$this->imageSaver = $imageSaver;

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

			$this->prepareOperations();

			if (!$this->imageSaver->savesAsync()) {

				$savedTransform = [];

				foreach ($this->operations as $name => $operation)

					$savedTransform[$name] = $this->imageSaver->transportImages($operation->getTransformed(), $name, $this->imageResourceName);

				return $savedTransform;
			}

			$newNames = $this->computeImageNames();

			$this->queueManager->augmentArguments(AsyncImageProcessor::class, [

				"operations" => $this->operations,

				"imageNames" => $newNames
			]);

			return $newNames;
		}

		private function computeImageNames ():array {

			$earlyNames = [];

			foreach ($this->operations as $operationName => $operation) {

				foreach ($this->originalImages as $image)

					$earlyNames[$operationName] = $this->imageSaver->resolveName($image, $operationName, $this->imageResourceName);
			}

			return $earlyNames;
		}

		private function prepareOperations ():void {

			foreach ($this->operations as $operation)

				$operation->setFiles($this->originalImages);
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

			$this->operations[__FUNCTION__] = $this->inferiorImage->setMaxSize($maxSize);

			return $this;
		}

		public function thumbnail (int $width, int $height):self {

			$this->operations[__FUNCTION__] = $this->thumbnailImage->setDimensions($width, $height);

			return $this;
		}
	}
?>