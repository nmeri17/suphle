<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers;

	use Tilwa\Services\ServiceCoordinator;

	use Tilwa\Services\ImageStorageService;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\PayloadReaders\ImageServiceConsumer;

	class ImageUploadController extends ServiceCoordinator {

		private $imageService;

		public function __construct (ImageStorageService $imageService) {

			$this->imageService = $imageService;
		}

		public function validatorCollection ():string {

			return ""; // add this
		}

		public function applyAllOptimizations (ImageServiceConsumer $payload):array {

			$resourceName = $payload->getDomainObject(); // since no computation happens, it's safe to use without checking for null

			return $this->imageService->getOptimizer($resourceName)

			->inferior(50) // in the test, assert that resulting file is <= this size
			->thumbnail(15, 15)->savedImageNames();
		}

		public function applyNoOptimization (ImageServiceConsumer $payload):array {

			$resourceName = $payload->getDomainObject();

			return $this->imageService->getOptimizer( $resourceName)

			->savedImageNames();
		}
	}
?>