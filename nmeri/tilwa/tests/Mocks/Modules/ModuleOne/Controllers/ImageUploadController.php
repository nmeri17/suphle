<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers;

	use Tilwa\Services\ServiceCoordinator;

	use Tilwa\Services\ImageStorageService;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{PayloadReaders\ImageServiceConsumer, Validators\ImageValidator};

	class ImageUploadController extends ServiceCoordinator {

		private $imageService;

		public function __construct (ImageStorageService $imageService) {

			$this->imageService = $imageService;
		}

		public function validatorCollection ():string {

			return ImageValidator::class;
		}

		public function applyAllOptimizations (ImageServiceConsumer $payload):array {

			$resourceName = $payload->getDomainObject(); // since no computation happens, it's safe to use without checking for null

			$fileNames = $this->imageService->getOptimizer($resourceName)

			->inferior(150) // in the test, assert that resulting file is <= this size
			->thumbnail(50, 50)->savedImageNames();

			return $fileNames;
		}

		public function applyNoOptimization (ImageServiceConsumer $payload):array {

			return $this->imageService->getOptimizer($payload->getDomainObject())

			->savedImageNames();
		}

		public function applyThumbnail (ImageServiceConsumer $payload):array {

			return $this->imageService

			->getOptimizer($payload->getDomainObject())

			->thumbnail(50, 50)->savedImageNames();
		}
	}
?>