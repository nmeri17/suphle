<?php
	namespace Tilwa\Services;

	use Tilwa\Contracts\Requests\FileInputReader;

	use Tilwa\IO\Image\OptimizersManager;

	class ImageStorageService extends UpdatelessService {

		private $imageOptimizer, $files;

		public function __construct (OptimizersManager $imageOptimizer, FileInputReader $inputReader) {

			$this->imageOptimizer = $imageOptimizer;

			$this->files = $inputReader->getFileObjects();
		}

		public function getOptimizer (string $resourceName):OptimizersManager {

			return $this->imageOptimizer->setImages($this->files, $resourceName);
		}
	}
?>