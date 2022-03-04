<?php
	namespace Tilwa\Adapters\Image\Optimizers;

	use Tilwa\Contracts\IO\InferiorImageContract;

	use ImageOptimizer\OptimizerFactory;

	use Psr\Http\Message\UploadedFileInterface;

	class ImageOptimizerClient implements InferiorImageContract {

		private $context;

		public function setupClient ():void {

			$this->context = (new OptimizerFactory())->get();
		}

		public function downgrade (string $currentPath):void {

			$this->context->optimize($currentPath);
		}

		public function moveDowngraded (UploadedFileInterface $image, ?string $newPath):string {

			$image->moveTo($newPath );

			return $newPath;
		}
	}
?>