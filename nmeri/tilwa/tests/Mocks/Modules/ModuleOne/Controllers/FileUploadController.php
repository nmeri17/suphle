<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers;

	use Tilwa\Services\ServiceCoordinator;

	use Tilwa\IO\Image\ImageOptimizer;

	class FileUploadController extends ServiceCoordinator {

		private $imageOptimizer;

		public function __construct (ImageOptimizer $imageOptimizer) {

			$this->imageOptimizer = $imageOptimizer;
		}

		public function validatorCollection ():string {

			return ""; // add this
		}

		public function applyAllOptimizations (PickAll $picker, ModellessPayload $payload):array { // modify this. it's an abstract class

			$resourceName = $payload->getDomainObject()->operationValue();

			return $this->imageOptimizer->setImages($picker->toOptimize(), $resourceName)

			->inferior(50) // in the test, assert that resulting file is <= this size
			->thumbnail(15, 15)->getNames();
		}
	}
?>