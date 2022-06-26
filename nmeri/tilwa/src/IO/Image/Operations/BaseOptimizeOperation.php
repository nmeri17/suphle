<?php
	namespace Tilwa\IO\Image\Operations;

	use Tilwa\Contracts\IO\Image\ImageOptimiseOperation;

	abstract class BaseOptimizeOperation implements ImageOptimiseOperation {

		protected $files = [], $client, $imageLocator, $resourceName,

		$operationName // using a property instead of a constant since we can't formalise such as a contract

		;

		public function getAsyncNames ( string $imageResourceName):array {

			return array_map(function ($image) use ( $imageResourceName) {

				return $this->imageLocator->resolveName(

					$image, $this->operationName, $resourceName
				);
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

		public function getOperationName ():string {

			return $this->operationName;
		}
	}
?>