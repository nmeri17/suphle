<?php
	namespace Tilwa\IO\Image\Operations;

	use Tilwa\Contracts\IO\Image\ImageOptimiseOperation;

	abstract class BaseOptimizeOperation implements ImageOptimiseOperation {

		protected $files, $client, $imageLocator, $operationName,

		$resourceName;

		public function getAsyncNames (string $operationName, string $imageResourceName):array {

			return array_map(function ($image) use ($operationName, $imageResourceName) {

				return $this->imageLocator->resolveName($image, $operationName, $resourceName);
			});
		}

		public function setFiles (array $images):void {

			$this->files = $images;
		}

		public function savesAsync ():bool {

			return false;
		}

		public function setResourceName (string $name):void {

			$this->resourceName = $name;
		}

		public function setName (string $name):void {

			$this->operationName = $name;
		}
	}
?>