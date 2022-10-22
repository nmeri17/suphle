<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{PayloadReaders\ImageServiceConsumer, Validators\ImageValidator};

	class ImageUploadCoordinator extends ServiceCoordinator {

		public function validatorCollection ():string {

			return ImageValidator::class;
		}

		public function applyAllOptimizations (ImageServiceConsumer $payload):array {

			$fileNames = $payload->getDomainObject() // since no computation happens, it's safe to use without checking for null

			->inferior(150) // in the test, assert that resulting file is <= this size
			->thumbnail(50, 50)->savedImageNames();

			return $fileNames;
		}

		public function applyNoOptimization (ImageServiceConsumer $payload):array {

			return $payload->getDomainObject()->savedImageNames();
		}

		public function applyThumbnail (ImageServiceConsumer $payload):array {

			return $payload->getDomainObject()->thumbnail(50, 50)

			->savedImageNames();
		}
	}
?>