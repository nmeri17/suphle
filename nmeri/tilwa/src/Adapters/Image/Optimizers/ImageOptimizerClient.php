<?php
	namespace Tilwa\Adapters\Image\Optimizers;

	use Tilwa\Contracts\IO\Image\InferiorImageClient;

	use ImageOptimizer\OptimizerFactory;

	use Psr\Http\Message\UploadedFileInterface;

	class ImageOptimizerClient implements InferiorImageClient {

		private $client;

		public function setupClient ():void {

			$this->client = (new OptimizerFactory())->get();
		}

		public function downgrade (string $currentPath):void {

			$this->client->optimize($currentPath);
		}

		public function moveDowngraded (UploadedFileInterface $image, ?string $newPath):string {

			$image->moveTo($newPath );

			return $newPath;
		}
	}
?>